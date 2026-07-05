<?php

namespace App\Services\Messaging;

use App\Services\Messaging\Contracts\WhatsAppProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Meta WhatsApp Business Cloud API.
 * Dokuman: https://developers.facebook.com/docs/whatsapp/cloud-api
 */
class MetaWhatsAppProvider implements WhatsAppProvider
{
    public function __construct(
        private readonly string $accessToken,
        private readonly string $phoneNumberId,
        private readonly string $apiVersion = 'v21.0',
    ) {}

    public function send(string $to, string $message): SendResult
    {
        if ($this->accessToken === '' || $this->phoneNumberId === '') {
            return SendResult::fail('missing_credentials');
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->timeout(15)
                ->post("https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => PhoneNumber::e164($to),
                    'type' => 'text',
                    'text' => ['body' => $message],
                ]);

            $json = $response->json();

            if ($response->successful() && isset($json['messages'][0]['id'])) {
                return SendResult::ok($json['messages'][0]['id']);
            }

            $error = $json['error']['message'] ?? ('http_'.$response->status());
            Log::warning('Meta WhatsApp gonderimi basarisiz', ['to' => $to, 'error' => $error]);

            return SendResult::fail($error);
        } catch (\Throwable $e) {
            Log::error('Meta WhatsApp istisna', ['message' => $e->getMessage()]);

            return SendResult::fail('exception: '.$e->getMessage());
        }
    }
}
