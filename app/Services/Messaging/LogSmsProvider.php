<?php

namespace App\Services\Messaging;

use App\Services\Messaging\Contracts\SmsProvider;
use Illuminate\Support\Facades\Log;

/** Gelistirme ortami: SMS'i log dosyasina yazar. */
class LogSmsProvider implements SmsProvider
{
    public function send(string $to, string $message): SendResult
    {
        Log::info('[SMS/log] mesaj', ['to' => PhoneNumber::e164($to), 'body' => $message]);

        return SendResult::ok('log-sms-'.uniqid());
    }
}
