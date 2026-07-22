<?php

namespace App\Services\Messaging;

use App\Services\Messaging\Contracts\SmsProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verimor SMS API v2.
 * Dokuman: https://developer.verimor.com.tr/smsapi
 */
class VerimorSmsProvider implements SmsProvider
{
    public function __construct(
        private readonly string $username,
        private readonly string $password,
        private readonly string $sourceAddr,
    ) {}

    public function send(string $to, string $message): SendResult
    {
        if ($this->username === '' || $this->password === '' || $this->sourceAddr === '') {
            return SendResult::fail('missing_credentials');
        }

        $dest = ltrim(PhoneNumber::e164($to), '+');

        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->post('https://sms.verimor.com.tr/v2/send.json', [
                    'username' => $this->username,
                    'password' => $this->password,
                    'source_addr' => $this->sourceAddr,
                    'messages' => [
                        ['msg' => $message, 'dest' => $dest],
                    ],
                ]);

            if ($response->successful()) {
                $body = trim((string) $response->body());

                return SendResult::ok($body !== '' ? $body : 'verimor-'.uniqid());
            }

            $error = trim((string) $response->body()) ?: ('http_'.$response->status());
            Log::warning('Verimor SMS gonderimi basarisiz', ['to' => $dest, 'error' => $error]);

            return SendResult::fail($error);
        } catch (\Throwable $e) {
            Log::error('Verimor SMS istisna', ['message' => $e->getMessage()]);

            return SendResult::fail('exception: '.$e->getMessage());
        }
    }
}
