<?php

namespace App\Http\Controllers\Booking;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Staff;
use Illuminate\Http\Response;

/**
 * Personel takvim beslemesi (iCalendar / RFC 5545).
 * Google Takvim, Apple Takvim ve Outlook "URL ile ekle" destekler.
 * Token gizli oldugu icin kimlik dogrulama gerektirmez.
 */
class CalendarFeedController extends Controller
{
    public function show(string $token): Response
    {
        $staff = Staff::withoutTenantScope()
            ->with('business:id,name,slug')
            ->where('ics_token', $token)
            ->firstOrFail();

        $appointments = Appointment::withoutTenantScope()
            ->with(['customer:id,name,phone', 'service:id,name'])
            ->where('staff_id', $staff->id)
            ->whereIn('status', [AppointmentStatus::Pending->value, AppointmentStatus::Confirmed->value, AppointmentStatus::Completed->value])
            ->whereBetween('starts_at', [now()->subDays(30), now()->addDays(90)])
            ->orderBy('starts_at')
            ->get();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Reserup//Randevu Takvimi//TR',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:'.$this->escape($staff->business->name.' — '.$staff->name),
            'X-WR-TIMEZONE:'.config('app.timezone'),
        ];

        foreach ($appointments as $appointment) {
            $summary = sprintf(
                '%s — %s',
                $appointment->service?->name ?? 'Randevu',
                $appointment->customer?->name ?? 'Müşteri',
            );

            $description = trim(sprintf(
                "Müşteri: %s\\nTelefon: %s\\nDurum: %s",
                $appointment->customer?->name ?? '-',
                $appointment->customer?->phone ?? '-',
                $appointment->status->label(),
            ));

            $lines = [...$lines,
                'BEGIN:VEVENT',
                'UID:reserup-apt-'.$appointment->id.'@reserup',
                'DTSTAMP:'.$appointment->updated_at->clone()->utc()->format('Ymd\THis\Z'),
                'DTSTART:'.$appointment->starts_at->clone()->utc()->format('Ymd\THis\Z'),
                'DTEND:'.$appointment->ends_at->clone()->utc()->format('Ymd\THis\Z'),
                'SUMMARY:'.$this->escape($summary),
                'DESCRIPTION:'.$this->escape($description),
                'STATUS:'.($appointment->status === AppointmentStatus::Pending ? 'TENTATIVE' : 'CONFIRMED'),
                'END:VEVENT',
            ];
        }

        $lines[] = 'END:VCALENDAR';

        // RFC: satirlar CRLF ile ayrilir
        $body = implode("\r\n", $lines)."\r\n";

        return response($body, 200, [
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="reserup-takvim.ics"',
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    private function escape(string $value): string
    {
        return str_replace([',', ';'], ['\,', '\;'], str_replace(["\r\n", "\n"], '\n', $value));
    }
}
