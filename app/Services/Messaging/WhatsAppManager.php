<?php

namespace App\Services\Messaging;

use App\Models\Business;
use App\Models\MessageLog;
use App\Models\SystemSetting;
use App\Services\Messaging\Contracts\WhatsAppProvider;
use Illuminate\Support\Facades\Crypt;

/**
 * Merkezi WhatsApp altyapisi: tum isletmeler, super admin'in
 * sistem ayarlarina girdigi TEK API uzerinden gonderim yapar.
 *
 * Isletme tarafinda yalnizca ac/kapat tercihi vardir; saglayici ve
 * kimlik bilgileri platform genelinde yonetilir. Plan bazli aylik
 * kota burada uygulanir.
 */
class WhatsAppManager
{
    public function provider(): WhatsAppProvider
    {
        $name = (string) SystemSetting::get('whatsapp_provider', 'log');

        return match ($name) {
            'meta' => new MetaWhatsAppProvider(
                self::apiKey(),
                (string) SystemSetting::get('whatsapp_phone_number_id', ''),
            ),
            'twilio' => new TwilioWhatsAppProvider(
                self::apiKey(),
                (string) SystemSetting::get('whatsapp_phone_number_id', ''),
            ),
            default => new LogWhatsAppProvider(),
        };
    }

    /** Isletme adina gonderim (kota + tercih kontrollu, message_logs'a islenir). */
    public function send(
        Business $business,
        string $to,
        string $message,
        string $messageType = 'manual',
        ?int $appointmentId = null,
    ): SendResult {
        $log = MessageLog::create([
            'business_id' => $business->id,
            'channel' => 'whatsapp',
            'recipient' => PhoneNumber::e164($to),
            'body' => $message,
            'message_type' => $messageType,
            'status' => 'queued',
            'appointment_id' => $appointmentId,
        ]);

        if (! $business->whatsapp_enabled) {
            $log->update(['status' => 'failed', 'provider_response' => 'whatsapp_disabled']);

            return SendResult::fail('whatsapp_disabled');
        }

        if ($this->quotaExceeded($business)) {
            $log->update(['status' => 'failed', 'provider_response' => 'quota_exceeded']);

            return SendResult::fail('quota_exceeded');
        }

        $result = $this->provider()->send($to, $message);

        $log->update([
            'status' => $result->success ? 'sent' : 'failed',
            'provider_response' => $result->success ? $result->providerId : $result->error,
            'sent_at' => $result->success ? now() : null,
        ]);

        return $result;
    }

    /** Platform testi (super admin ayar sayfasi) — kota ve isletme tercihi uygulanmaz. */
    public function sendPlatformTest(string $to, string $message): SendResult
    {
        $log = MessageLog::create([
            'business_id' => null,
            'channel' => 'whatsapp',
            'recipient' => PhoneNumber::e164($to),
            'body' => $message,
            'message_type' => 'system',
            'status' => 'queued',
        ]);

        $result = $this->provider()->send($to, $message);

        $log->update([
            'status' => $result->success ? 'sent' : 'failed',
            'provider_response' => $result->success ? $result->providerId : $result->error,
            'sent_at' => $result->success ? now() : null,
        ]);

        return $result;
    }

    /** Bu ay gonderilen mesaj sayisi plan kotasini asti mi? (0 = sinirsiz) */
    public function quotaExceeded(Business $business): bool
    {
        $quota = (int) ($business->plan?->whatsapp_quota_monthly ?? 0);

        if ($quota <= 0) {
            return false;
        }

        return $this->usedThisMonth($business) >= $quota;
    }

    public function usedThisMonth(Business $business): int
    {
        return MessageLog::query()
            ->where('business_id', $business->id)
            ->where('channel', 'whatsapp')
            ->where('status', 'sent')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
    }

    /** API anahtari sistem ayarlarinda sifreli tutulur. */
    public static function storeApiKey(string $plainKey): void
    {
        SystemSetting::set('whatsapp_api_key', Crypt::encryptString($plainKey));
    }

    private static function apiKey(): string
    {
        $stored = (string) SystemSetting::get('whatsapp_api_key', '');

        if ($stored === '') {
            return '';
        }

        try {
            return Crypt::decryptString($stored);
        } catch (\Throwable) {
            return '';
        }
    }
}
