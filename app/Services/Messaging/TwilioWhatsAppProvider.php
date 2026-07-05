<?php

namespace App\Services\Messaging;

use App\Services\Messaging\Contracts\WhatsAppProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Twilio WhatsApp API.
 * api_key formati: "ACCOUNT_SID:AUTH_TOKEN", phone_number_id: gonderen numara (+1415...)
 */
class TwilioWhatsAppProvider implements WhatsAppProvider
{
    private string $accountSid;

    private string $authToken;

    public function __construct(
        string $apiKey,
        private readonly string $fromNumber,
    ) {
        [$this->accountSid, $this->authToken] = array_pad(explode(':', $apiKey, 2), 2, '');
    }

    public function send(string $to, string $message): SendResult
    {
        if ($this->accountSid === '' || $this->authToken === '' || $this->fromNumber === '') {
            return SendResult::fail('missing_credentials');
        }

        try {
            $response = Http::asForm()
                ->withBasicAuth($this->accountSid, $this->authToken)
                ->timeout(15)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json", [
                    'From' => 'whatsapp:'.PhoneNumber::e164($this->fromNumber),
                    'To' => 'whatsapp:'.PhoneNumber::e164($to),
                    'Body' => $message,
                ]);

            $json = $response->json();

            if ($response->successful() && isset($json['sid'])) {
                return SendResult::ok($json['sid']);
            }

            $error = $json['message'] ?? ('http_'.$response->status());
            Log::warning('Twilio WhatsApp gonderimi basarisiz', ['to' => $to, 'error' => $error]);

            return SendResult::fail($error);
        } catch (\Throwable $e) {
            Log::error('Twilio WhatsApp istisna', ['message' => $e->getMessage()]);

            return SendResult::fail('exception: '.$e->getMessage());
        }
    }
}
