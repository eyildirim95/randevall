<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\CalendarClosure;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Business $business, Request $request): View
    {
        $user = $request->user();

        // Personel rolu yalnizca kendi takvimini gorur
        $lockedStaffId = $user->isStaffOnly($business) ? $user->staffIdIn($business) : null;

        $prefillCustomer = null;

        if ($request->filled('customer_id')) {
            $prefillCustomer = Customer::query()
                ->whereKey($request->integer('customer_id'))
                ->first(['id', 'name', 'phone']);
        }

        return view('panel.calendar', [
            'business' => $business,
            'staffList' => Staff::query()->active()->ordered()->get(),
            'services' => Service::query()->active()->ordered()->get(),
            'lockedStaffId' => $lockedStaffId,
            'prefillCustomer' => $prefillCustomer,
        ]);
    }

    /** FullCalendar olay kaynagi (randevular + kapatmalar). */
    public function events(Business $business, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date'],
            'staff_id' => ['nullable', 'integer'],
        ]);

        $start = Carbon::parse($validated['start']);
        $end = Carbon::parse($validated['end']);

        // Personel rolu icin filtre kendi kartina sabitlenir
        $user = $request->user();

        if ($user->isStaffOnly($business)) {
            $validated['staff_id'] = $user->staffIdIn($business) ?? -1;
        }

        $appointments = Appointment::query()
            ->with(['customer:id,name,phone', 'service:id,name,color', 'staff:id,name,color'])
            ->between($start, $end)
            ->when($validated['staff_id'] ?? null, fn ($q, $id) => $q->where('staff_id', $id))
            ->get()
            ->map(fn (Appointment $a) => [
                'id' => 'apt-'.$a->id,
                'appointmentId' => $a->id,
                'title' => $a->customer->name.' · '.($a->service?->name ?? 'Hizmet'),
                'start' => $a->starts_at->toIso8601String(),
                'end' => $a->ends_at->toIso8601String(),
                'backgroundColor' => $a->status->value === 'cancelled' || $a->status->value === 'no_show'
                    ? '#adb5bd'
                    : ($a->staff?->color ?? '#7f56da'),
                'borderColor' => 'transparent',
                'editable' => in_array($a->status->value, ['pending', 'confirmed'], true),
                'extendedProps' => [
                    'type' => 'appointment',
                    'status' => $a->status->value,
                    'statusLabel' => $a->status->label(),
                    'customer' => $a->customer->name,
                    'phone' => $a->customer->phone,
                    'service' => $a->service?->name,
                    'staff' => $a->staff?->name,
                    'staffId' => $a->staff_id,
                    'price' => (float) $a->price,
                    'notes' => $a->notes,
                ],
            ]);

        $closures = CalendarClosure::query()
            ->with('staff:id,name')
            ->between($start, $end)
            ->when($validated['staff_id'] ?? null, fn ($q, $id) => $q->where(
                fn ($qq) => $qq->whereNull('staff_id')->orWhere('staff_id', $id)
            ))
            ->get()
            ->map(fn (CalendarClosure $c) => [
                'id' => 'cls-'.$c->id,
                'title' => '🔒 '.($c->reason ?: 'Kapalı').($c->staff ? ' ('.$c->staff->name.')' : ''),
                'start' => $c->starts_at->toIso8601String(),
                'end' => $c->ends_at->toIso8601String(),
                'display' => 'background',
                'backgroundColor' => '#dc3545',
                'extendedProps' => ['type' => 'closure'],
            ]);

        return response()->json($appointments->concat($closures)->values());
    }
}
