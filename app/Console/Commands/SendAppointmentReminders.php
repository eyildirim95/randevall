<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\Notifications\AppointmentNotifier;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'reserup:send-reminders';

    protected $description = 'Yaklasan randevular icin WhatsApp/e-posta hatirlatmasi gonderir';

    public function handle(AppointmentNotifier $notifier): int
    {
        // Tenant baglami yok; tum isletmeler taranir.
        $appointments = Appointment::query()
            ->with(['business', 'customer', 'service', 'staff'])
            ->whereNull('reminder_sent_at')
            ->whereIn('status', [AppointmentStatus::Pending->value, AppointmentStatus::Confirmed->value])
            ->where('starts_at', '>', now())
            ->whereHas('business', fn ($q) => $q->where('is_active', true)->whereNull('suspended_at'))
            ->get()
            ->filter(function (Appointment $a) {
                $hoursBefore = (int) $a->business->reminder_hours_before;

                return $hoursBefore > 0
                    && now()->greaterThanOrEqualTo($a->starts_at->copy()->subHours($hoursBefore));
            });

        foreach ($appointments as $appointment) {
            $notifier->reminder($appointment);
        }

        $this->info("Gonderilen hatirlatma: {$appointments->count()}");

        return self::SUCCESS;
    }
}
