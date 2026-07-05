<?php

namespace App\Http\Controllers\Panel;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\Service;
use App\Models\Staff;
use App\Services\Booking\BookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(private readonly BookingService $booking) {}

    /** Personel rolu yalnizca kendi randevusuna dokunabilir. */
    private function guardOwnAppointment(Business $business, Appointment $appointment, Request $request): void
    {
        $user = $request->user();

        if ($user->isStaffOnly($business) && $appointment->staff_id !== $user->staffIdIn($business)) {
            abort(403, 'Yalnızca kendi randevularınıza erişebilirsiniz.');
        }
    }

    public function index(Business $business, Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(AppointmentStatus::class)],
            'staff_id' => ['nullable', 'integer'],
            'date' => ['nullable', 'date'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        // Personel rolu yalnizca kendi randevularini listeler
        $user = $request->user();

        if ($user->isStaffOnly($business)) {
            $filters['staff_id'] = $user->staffIdIn($business) ?? -1;
        }

        $appointments = Appointment::query()
            ->with(['customer', 'service', 'staff'])
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['staff_id'] ?? null, fn ($q, $id) => $q->where('staff_id', $id))
            ->when($filters['date'] ?? null, fn ($q, $d) => $q->whereDate('starts_at', $d))
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->whereHas(
                'customer',
                fn ($c) => $c->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%")
            ))
            ->orderByDesc('starts_at')
            ->paginate(20)
            ->withQueryString();

        return view('panel.appointments.index', [
            'business' => $business,
            'appointments' => $appointments,
            'staffList' => Staff::query()->ordered()->get(),
            'filters' => $filters,
        ]);
    }

    public function store(Business $business, Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')->where('business_id', $business->id)],
            'customer_name' => ['required_without:customer_id', 'nullable', 'string', 'max:120'],
            'customer_phone' => ['required_without:customer_id', 'nullable', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:190'],
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('business_id', $business->id)],
            'staff_id' => ['required', 'integer', Rule::exists('staff', 'id')->where('business_id', $business->id)],
            'starts_at' => ['required', 'date', 'after:-1 hour'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'force' => ['nullable', 'boolean'],
            'repeat_weeks' => ['nullable', 'integer', Rule::in([0, 1, 2, 4])],
            'repeat_count' => ['nullable', 'integer', 'between:2,12'],
        ]);

        $user = $request->user();

        // Personel rolu yalnizca kendi adina randevu olusturabilir
        if ($user->isStaffOnly($business)) {
            $data['staff_id'] = $user->staffIdIn($business) ?? abort(403);
        }

        $repeatWeeks = (int) ($data['repeat_weeks'] ?? 0);
        $repeatCount = (int) ($data['repeat_count'] ?? 0);
        $enforceHours = ! ($data['force'] ?? false);

        // Panelde calisma saati disina randevu yazilabilir (force), cakisma her zaman engellenir
        if ($repeatWeeks > 0 && $repeatCount >= 2) {
            $result = $this->booking->createSeries($business, $data, $repeatWeeks, $repeatCount, $user->id, $enforceHours);
            $appointment = $result['appointment'];

            $message = "{$result['created']} randevuluk seri oluşturuldu.";

            if ($result['skipped']) {
                $message .= ' Çakışan tarihler atlandı: '.implode(', ', $result['skipped']);
            }
        } else {
            $appointment = $this->booking->create($business, $data, 'panel', $user->id, $enforceHours);
            $message = 'Randevu oluşturuldu.';
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'id' => $appointment->id, 'message' => $message], 201);
        }

        return back()->with('success', $message);
    }

    public function show(Business $business, Appointment $appointment, Request $request): View
    {
        $this->guardOwnAppointment($business, $appointment, $request);

        $appointment->load(['customer', 'service', 'staff', 'statusLogs.changedBy']);

        return view('panel.appointments.show', compact('business', 'appointment'));
    }

    public function update(Business $business, Appointment $appointment, Request $request): RedirectResponse
    {
        $this->guardOwnAppointment($business, $appointment, $request);

        $data = $request->validate([
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('business_id', $business->id)],
            'staff_id' => ['required', 'integer', Rule::exists('staff', 'id')->where('business_id', $business->id)],
            'starts_at' => ['required', 'date'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $service = Service::query()->findOrFail($data['service_id']);
        $startsAt = Carbon::parse($data['starts_at']);

        $this->booking->move(
            $appointment,
            $startsAt,
            (int) $data['staff_id'],
            $startsAt->copy()->addMinutes($service->totalMinutes()),
        );

        $appointment->forceFill([
            'service_id' => $service->id,
            'price' => $data['price'],
            'notes' => $data['notes'] ?? null,
        ])->save();

        return back()->with('success', 'Randevu güncellendi.');
    }

    /** Takvimde surukle-birak / yeniden boyutlandirma. */
    public function move(Business $business, Appointment $appointment, Request $request): JsonResponse
    {
        $this->guardOwnAppointment($business, $appointment, $request);

        $data = $request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'staff_id' => ['nullable', 'integer', Rule::exists('staff', 'id')->where('business_id', $business->id)],
        ]);

        $this->booking->move(
            $appointment,
            Carbon::parse($data['starts_at']),
            $data['staff_id'] ?? null,
            isset($data['ends_at']) ? Carbon::parse($data['ends_at']) : null,
        );

        return response()->json(['ok' => true]);
    }

    public function updateStatus(Business $business, Appointment $appointment, Request $request): RedirectResponse|JsonResponse
    {
        $this->guardOwnAppointment($business, $appointment, $request);

        $data = $request->validate([
            'status' => ['required', Rule::enum(AppointmentStatus::class)],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $newStatus = AppointmentStatus::from($data['status']);

        if ($newStatus === AppointmentStatus::Cancelled) {
            $this->booking->cancel($appointment, $data['reason'] ?? null, $request->user()->id);
        } else {
            $this->booking->updateStatus($appointment, $newStatus, $request->user()->id, $data['reason'] ?? null);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Randevu durumu güncellendi.');
    }

    public function destroy(Business $business, Appointment $appointment, Request $request): RedirectResponse
    {
        // Randevu silme yalnizca yonetici yetkisidir
        $user = $request->user();

        abort_unless($user->canManage($business) || $user->isSuperAdmin(), 403);

        $appointment->delete();

        return redirect()
            ->route('panel.appointments.index', $business)
            ->with('success', 'Randevu silindi.');
    }
}
