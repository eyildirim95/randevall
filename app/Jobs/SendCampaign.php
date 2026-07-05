<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\Customer;
use App\Services\Messaging\WhatsAppManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Kampanya mesajlarini hedef kitleye gonderir.
 * {ad} yer tutucusu musteri adiyla degistirilir; kota asilirsa durur.
 */
class SendCampaign implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    public function __construct(public readonly int $campaignId) {}

    public function handle(WhatsAppManager $whatsapp): void
    {
        $campaign = Campaign::withoutTenantScope()->with('business')->find($this->campaignId);

        if (! $campaign || $campaign->status === 'sent') {
            return;
        }

        $business = $campaign->business;
        $campaign->forceFill(['status' => 'sending'])->save();

        $sent = 0;
        $failed = 0;

        $this->audienceQuery($campaign)->chunkById(100, function ($customers) use ($whatsapp, $business, $campaign, &$sent, &$failed) {
            foreach ($customers as $customer) {
                $message = str_replace('{ad}', $customer->name, $campaign->message);

                $result = $whatsapp->send($business, $customer->phone, $message, 'campaign');

                $result->success ? $sent++ : $failed++;

                // Kota dolduysa devam etmenin anlami yok
                if (! $result->success && $result->error === 'quota_exceeded') {
                    return false;
                }
            }
        });

        $campaign->forceFill([
            'status' => 'sent',
            'sent_count' => $sent,
            'failed_count' => $failed,
            'sent_at' => now(),
        ])->save();
    }

    private function audienceQuery(Campaign $campaign)
    {
        $query = Customer::withoutTenantScope()
            ->where('business_id', $campaign->business_id)
            ->where('is_blacklisted', false)
            ->whereNotNull('phone');

        return match ($campaign->audience) {
            'loyal' => $query->where('completed_appointments', '>=', 3),
            'recent' => $query->whereHas('appointments', fn ($q) => $q
                ->withoutGlobalScope('tenant')
                ->where('starts_at', '>=', now()->subDays(90))),
            default => $query,
        };
    }
}
