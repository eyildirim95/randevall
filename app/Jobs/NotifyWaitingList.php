<?php

namespace App\Jobs;

use App\Models\Business;
use App\Models\WaitingListEntry;
use App\Services\Messaging\WhatsAppManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Randevu iptalinde ayni gunu bekleyen musterilere
 * "yer acildi" bildirimi gonderir.
 */
class NotifyWaitingList implements ShouldQueue
{
    use Queueable;

    private const MAX_NOTIFICATIONS = 5;

    public function __construct(
        public readonly int $businessId,
        public readonly string $date,
        public readonly ?int $staffId = null,
    ) {}

    public function handle(WhatsAppManager $whatsapp): void
    {
        $business = Business::find($this->businessId);

        if (! $business || ! $business->whatsapp_enabled) {
            return;
        }

        $entries = WaitingListEntry::withoutTenantScope()
            ->where('business_id', $business->id)
            ->waiting()
            ->whereDate('preferred_date', $this->date)
            ->where(function ($q) {
                $q->whereNull('staff_id');
                if ($this->staffId) {
                    $q->orWhere('staff_id', $this->staffId);
                }
            })
            ->orderBy('created_at')
            ->limit(self::MAX_NOTIFICATIONS)
            ->get();

        foreach ($entries as $entry) {
            $result = $whatsapp->send(
                $business,
                $entry->customer_phone,
                sprintf(
                    "Merhaba %s! %s işletmesinde beklediğiniz %s tarihinde bir yer açıldı. 🎉\n\nHemen randevu almak için: %s",
                    $entry->customer_name,
                    $business->name,
                    $entry->preferred_date->translatedFormat('d F Y l'),
                    route('booking.show', $business->slug),
                ),
                'waitlist_slot_opened',
            );

            if ($result->success) {
                $entry->forceFill(['status' => 'notified', 'notified_at' => now()])->save();
            }
        }
    }
}
