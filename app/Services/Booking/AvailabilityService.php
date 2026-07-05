<?php

namespace App\Services\Booking;

use App\Models\Appointment;
use App\Models\Business;
use App\Models\CalendarClosure;
use App\Models\Service;
use App\Models\Staff;
use App\Models\WorkingHour;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * Musaitlik hesaplama: calisma saatleri, molalar, takvim kapatmalari,
 * mevcut randevular, min. on sure ve slot araligini birlikte degerlendirir.
 */
class AvailabilityService
{
    /**
     * Belirli gun icin uygun baslangic saatlerini dondurur.
     *
     * @return Collection<int, string> "HH:MM" listesi
     */
    public function slotsFor(Business $business, Service $service, Staff $staff, Carbon $date): Collection
    {
        $duration = $service->totalMinutes();
        $interval = max(5, (int) $business->slot_interval_minutes);

        $window = $this->workingWindow($business, $staff, $date);

        if (! $window) {
            return collect();
        }

        [$dayStart, $dayEnd, $breakStart, $breakEnd] = $window;

        $earliest = now()->addMinutes((int) $business->min_notice_minutes);
        $latestDay = today()->addDays((int) $business->max_advance_days)->endOfDay();

        if ($date->copy()->endOfDay()->lessThan($earliest->copy()->startOfDay()) || $date->greaterThan($latestDay)) {
            return collect();
        }

        $appointments = Appointment::query()
            ->where('staff_id', $staff->id)
            ->blocking()
            ->between($dayStart, $dayEnd)
            ->get(['starts_at', 'ends_at']);

        $closures = CalendarClosure::query()
            ->forStaff($staff->id)
            ->between($dayStart, $dayEnd)
            ->get(['starts_at', 'ends_at']);

        $slots = collect();

        foreach (CarbonPeriod::create($dayStart, "{$interval} minutes", $dayEnd) as $slotStart) {
            $slotEnd = $slotStart->copy()->addMinutes($duration);

            if ($slotEnd->greaterThan($dayEnd)) {
                break;
            }

            if ($slotStart->lessThan($earliest)) {
                continue;
            }

            if ($breakStart && $slotStart->lessThan($breakEnd) && $slotEnd->greaterThan($breakStart)) {
                continue;
            }

            $overlapsAppointment = $appointments->contains(
                fn ($a) => $slotStart->lessThan($a->ends_at) && $slotEnd->greaterThan($a->starts_at)
            );

            $overlapsClosure = $closures->contains(
                fn ($c) => $slotStart->lessThan($c->ends_at) && $slotEnd->greaterThan($c->starts_at)
            );

            if (! $overlapsAppointment && ! $overlapsClosure) {
                $slots->push($slotStart->format('H:i'));
            }
        }

        return $slots;
    }

    /**
     * Verilen araligin rezerve edilebilir oldugunu dogrular.
     * $ignoreAppointmentId: guncelleme/tasima sirasinda kendi kaydini yok say.
     */
    public function isAvailable(
        Business $business,
        Staff $staff,
        Carbon $startsAt,
        Carbon $endsAt,
        ?int $ignoreAppointmentId = null,
        bool $enforceWorkingHours = true,
    ): bool {
        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            return false;
        }

        if ($enforceWorkingHours) {
            $window = $this->workingWindow($business, $staff, $startsAt->copy()->startOfDay());

            if (! $window) {
                return false;
            }

            [$dayStart, $dayEnd, $breakStart, $breakEnd] = $window;

            if ($startsAt->lessThan($dayStart) || $endsAt->greaterThan($dayEnd)) {
                return false;
            }

            if ($breakStart && $startsAt->lessThan($breakEnd) && $endsAt->greaterThan($breakStart)) {
                return false;
            }
        }

        $hasClosure = CalendarClosure::query()
            ->forStaff($staff->id)
            ->between($startsAt, $endsAt)
            ->exists();

        if ($hasClosure) {
            return false;
        }

        return ! Appointment::query()
            ->where('staff_id', $staff->id)
            ->when($ignoreAppointmentId, fn ($q) => $q->where('id', '!=', $ignoreAppointmentId))
            ->blocking()
            ->between($startsAt, $endsAt)
            ->exists();
    }

    /**
     * Gunun calisma penceresi: [baslangic, bitis, mola baslangic, mola bitis]
     * Personel ozel saati yoksa isletme geneli kullanilir. Kapali gunde null.
     *
     * @return array{0: Carbon, 1: Carbon, 2: ?Carbon, 3: ?Carbon}|null
     */
    public function workingWindow(Business $business, Staff $staff, Carbon $date): ?array
    {
        $dow = $date->dayOfWeek;

        $hour = WorkingHour::query()
            ->where('day_of_week', $dow)
            ->where(function ($q) use ($staff) {
                $q->where('staff_id', $staff->id)->orWhereNull('staff_id');
            })
            ->orderByRaw('staff_id IS NULL') // personel ozel kayit oncelikli
            ->first();

        if (! $hour || $hour->is_closed || ! $hour->start_time || ! $hour->end_time) {
            return null;
        }

        $dayStart = $date->copy()->setTimeFromTimeString($hour->start_time);
        $dayEnd = $date->copy()->setTimeFromTimeString($hour->end_time);

        $breakStart = $hour->break_start ? $date->copy()->setTimeFromTimeString($hour->break_start) : null;
        $breakEnd = $hour->break_end ? $date->copy()->setTimeFromTimeString($hour->break_end) : null;

        return [$dayStart, $dayEnd, $breakStart, $breakEnd];
    }
}
