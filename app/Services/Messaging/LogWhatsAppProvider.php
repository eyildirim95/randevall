<?php

namespace App\Services\Messaging;

use App\Services\Messaging\Contracts\WhatsAppProvider;
use Illuminate\Support\Facades\Log;

/** Gelistirme/demo ortami: mesaji log dosyasina yazar. */
class LogWhatsAppProvider implements WhatsAppProvider
{
    public function send(string $to, string $message): SendResult
    {
        Log::info('[WhatsApp/log] mesaj', ['to' => PhoneNumber::e164($to), 'body' => $message]);

        return SendResult::ok('log-'.uniqid());
    }
}
