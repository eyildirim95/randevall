<?php

namespace App\Http\Controllers\Booking;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\Booking\AvailabilityService;
use App\Services\Booking\BookingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Musterinin token linki ile randevusunu goruntulemesi, iptali,
 * yeniden planlamasi ve degerlendirmesi.
 * Token UUID oldugu icin tahmin edilemez; ayrica oran sinirlamasi uygulanir.
 */
class PublicAppointmentController extends Controller
{
    public function show(string $token): View
    {
        $appointment = $this->findByToken($token);

        return view('booking.appointment', [
            'appointment' => $appointment,
            'business' => $appointment->business,
        ]);
    }

    public function cancel(string $token, BookingService $booking): RedirectResponse
    {
        $appointment = $this->findByToken($token);

        if (! $appointment->isCancellable()) {
            return back()->withErrors(['cancel' => 'Bu randevu artık iptal edilemez.']);
        }

        $booking->cancel($appointment, 'Müşteri tarafından iptal edildi', null, false);

        \App\Services\Notifications\PanelNotifier::notify(
            $appointment->business,
            'customer_cancelled',
            sprintf('%s randevusunu iptal etti (%s)', $appointment->customer->name, $appointment->starts_at->format('d.m.Y H:i')),
            route('panel.appointments.show', [$appointment->business, $appointment]),
        );

        return back()->with('success', 'Randevunuz iptal edildi.');
    }

    /** Yeniden planlama: gun secimi + uygun saat listesi (sunucu tarafli). */
    public function rescheduleForm(string $token, Request $request, AvailabilityService $availability): View|RedirectResponse
    {
        $appointment = $this->findByToken($token);
        $business = $appointment->business;

        if (! $appointment->isCancellable() || ! $appointment->staff || ! $appointment->service) {
            return redirect()
                ->route('appointment.public.show', $token)
                ->withErrors(['reschedule' => 'Bu randevu yeniden planlanamaz. Lütfen işletmeyi arayın.']);
        }

        $validated = $request->validate([
            'date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $date = isset($validated['date']) ? Carbon::parse($validated['date'])->startOfDay() : null;

        $slots = collect();

        if ($date && $date->lessThanOrEqualTo(today()->addDays((int) $business->max_advance_days))) {
            $slots = $availability->slotsFor($business, $appointment->service, $appointment->staff, $date);
        }

        return view('booking.reschedule', [
            'appointment' => $appointment,
            'business' => $business,
            'date' => $date,
            'slots' => $slots,
        ]);
    }

    public function reschedule(string $token, Request $request, BookingService $booking): RedirectResponse
    {
        $appointment = $this->findByToken($token);

        if (! $appointment->isCancellable()) {
            return redirect()
                ->route('appointment.public.show', $token)
                ->withErrors(['reschedule' => 'Bu randevu artık değiştirilemez.']);
        }

        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
        ]);

        try {
            $booking->move($appointment, Carbon::parse($data['date'].' '.$data['time']));
        } catch (ValidationException) {
            return back()->withErrors(['time' => 'Seçilen saat dolu. Lütfen başka bir saat seçin.']);
        }

        // Guncel bilgilerle onay bildirimi gonder
        app(\App\Services\Notifications\AppointmentNotifier::class)->confirmation($appointment->fresh(['business', 'customer', 'service', 'staff']));

        return redirect()
            ->route('appointment.public.show', $token)
            ->with('success', 'Randevunuz yeni tarihe taşındı.');
    }

    /** Degerlendirme formu (yalnizca tamamlanan ve puanlanmamis randevu). */
    public function ratingForm(string $token): View|RedirectResponse
    {
        $appointment = $this->findByToken($token);

        if ($appointment->status !== AppointmentStatus::Completed) {
            return redirect()->route('appointment.public.show', $token);
        }

        if ($appointment->rated_at) {
            return redirect()
                ->route('appointment.public.show', $token)
                ->with('success', 'Değerlendirmeniz için teşekkürler!');
        }

        return view('booking.rate', [
            'appointment' => $appointment,
            'business' => $appointment->business,
        ]);
    }

    public function rate(string $token, Request $request): RedirectResponse
    {
        $appointment = $this->findByToken($token);

        if ($appointment->status !== AppointmentStatus::Completed || $appointment->rated_at) {
            return redirect()->route('appointment.public.show', $token);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'rating_comment' => ['nullable', 'string', 'max:500'],
        ]);

        $appointment->forceFill([
            'rating' => $data['rating'],
            'rating_comment' => $data['rating_comment'] ?? null,
            'rated_at' => now(),
        ])->save();

        return redirect()
            ->route('appointment.public.show', $token)
            ->with('success', 'Değerlendirmeniz alındı, teşekkürler! ⭐');
    }

    private function findByToken(string $token): Appointment
    {
        return Appointment::query()
            ->with(['business', 'customer', 'service', 'staff'])
            ->where('public_token', $token)
            ->firstOrFail();
    }
}
