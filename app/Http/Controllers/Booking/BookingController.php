<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Services\Booking\AvailabilityService;
use App\Services\Booking\BookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Musterilerin link uzerinden randevu aldigi public sayfa.
 */
class BookingController extends Controller
{
    public function show(Business $business): View
    {
        abort_unless($business->online_booking_enabled, 404);

        // Aboneligi dolan isletmenin rezervasyon sayfasi nazikce kapatilir
        if (! $business->hasActivePlan()) {
            return view('booking.unavailable', compact('business'));
        }

        $services = Service::query()
            ->active()
            ->where('online_bookable', true)
            ->with(['staff' => fn ($q) => $q->active()->where('accepts_online_booking', true)->ordered()])
            ->ordered()
            ->get();

        // Musteri degerlendirmeleri (sosyal kanit)
        $ratingStats = \App\Models\Appointment::query()
            ->whereNotNull('rating')
            ->selectRaw('AVG(rating) as avg, COUNT(*) as total')
            ->first();

        return view('booking.show', compact('business', 'services', 'ratingStats'));
    }

    /** Secilen hizmet+personel+gun icin uygun saatler (AJAX). */
    public function slots(Business $business, Request $request, AvailabilityService $availability): JsonResponse
    {
        abort_unless($business->online_booking_enabled && $business->hasActivePlan(), 404);

        $data = $request->validate([
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('business_id', $business->id)->where('is_active', true)->where('online_bookable', true)],
            'staff_id' => ['required', 'integer', Rule::exists('staff', 'id')->where('business_id', $business->id)->where('is_active', true)->where('accepts_online_booking', true)],
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $service = Service::query()->findOrFail($data['service_id']);
        $staff = Staff::query()->findOrFail($data['staff_id']);
        $date = Carbon::parse($data['date'])->startOfDay();

        if ($date->greaterThan(today()->addDays((int) $business->max_advance_days))) {
            return response()->json(['slots' => []]);
        }

        return response()->json([
            'slots' => $availability->slotsFor($business, $service, $staff, $date)->values(),
        ]);
    }

    public function store(Business $business, Request $request, BookingService $booking): RedirectResponse
    {
        abort_unless($business->online_booking_enabled && $business->hasActivePlan(), 404);

        $data = $request->validate([
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('business_id', $business->id)->where('is_active', true)->where('online_bookable', true)],
            'staff_id' => ['required', 'integer', Rule::exists('staff', 'id')->where('business_id', $business->id)->where('is_active', true)->where('accepts_online_booking', true)],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'min:10', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:190'],
            'notes' => ['nullable', 'string', 'max:500'],
            'kvkk' => ['accepted'],
        ]);

        $startsAt = Carbon::parse($data['date'].' '.$data['time']);

        $appointment = $booking->create($business, [
            'service_id' => $data['service_id'],
            'staff_id' => $data['staff_id'],
            'starts_at' => $startsAt,
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'customer_email' => $data['customer_email'] ?? null,
            'notes' => $data['notes'] ?? null,
        ], source: 'online');

        return redirect()->route('appointment.public.show', $appointment->public_token)
            ->with('booked', true);
    }

    /** Dolu gunde bekleme listesine kayit. */
    public function joinWaitlist(Business $business, Request $request): RedirectResponse
    {
        abort_unless($business->online_booking_enabled && $business->hasActivePlan(), 404);

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'min:10', 'max:30'],
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')->where('business_id', $business->id)],
            'staff_id' => ['nullable', 'integer', Rule::exists('staff', 'id')->where('business_id', $business->id)],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $normalizedPhone = \App\Services\Messaging\PhoneNumber::e164($data['customer_phone']);

        // Ayni telefon ayni gun icin tek kayit
        $exists = \App\Models\WaitingListEntry::query()
            ->waiting()
            ->where('customer_phone', $normalizedPhone)
            ->whereDate('preferred_date', $data['preferred_date'])
            ->exists();

        if (! $exists) {
            $entry = new \App\Models\WaitingListEntry([
                ...$data,
                'customer_phone' => $normalizedPhone,
            ]);
            $entry->business_id = $business->id;
            $entry->save();

            \App\Services\Notifications\PanelNotifier::notify(
                $business,
                'waitlist_joined',
                sprintf('%s bekleme listesine katıldı (%s)', $entry->customer_name, $entry->preferred_date->format('d.m.Y')),
                route('panel.waitlist.index', $business),
            );
        }

        return back()->with('success', 'Bekleme listesine eklendiniz. Yer açılırsa WhatsApp ile haber vereceğiz.');
    }
}
