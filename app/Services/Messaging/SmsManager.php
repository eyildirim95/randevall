<?php

namespace App\Services\Messaging;

use App\Models\Business;
use App\Models\MessageLog;
use App\Models\SystemSetting;
use App\Services\Messaging\Contracts\SmsProvider;
use Illuminate\Support\Facades\Crypt;

/**
 * Merkezi SMS altyapisi (Verimor). Tum isletmeler platform
 * ayarlarindaki tek hesap uzerinden gonderim yapar.
 */
class SmsManager
{
    public function provider(): SmsProvider
    {
        $name = (string) SystemSetting::get('sms_provider', 'log');

        return match ($name) {
            'verimor' => new VerimorSmsProvider(
                (string) SystemSetting::get('sms_username', ''),
                self::password(),
                (string) SystemSetting::get('sms_source_addr', ''),
            ),
            default => new LogSmsProvider(),
        };
    }

    public function send(
        Business $business,
        string $to,
        string $message,
        string $messageType = 'manual',
        ?int $appointmentId = null,
    ): SendResult {
        $log = MessageLog::create([
            'business_id' => $business->id,
            'channel' => 'sms',
            'recipient' => PhoneNumber::e164($to),
            'body' => $message,
            'message_type' => $messageType,
            'status' => 'queued',
            'appointment_id' => $appointmentId,
        ]);

        if (! $business->sms_enabled) {
            $log->update(['status' => 'failed', 'provider_response' => 'sms_disabled']);

            return SendResult::fail('sms_disabled');
        }

        if (MessageQuota::exceeded($business)) {
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

    public function sendPlatformTest(string $to, string $message): SendResult
    {
        $log = MessageLog::create([
            'business_id' => null,
            'channel' => 'sms',
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

    public static function storePassword(string $plainPassword): void
    {
        SystemSetting::set('sms_password', Crypt::encryptString($plainPassword));
    }

    private static function password(): string
    {
        $stored = (string) SystemSetting::get('sms_password', '');

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
