<?php

namespace App\Services\Messaging\Contracts;

use App\Services\Messaging\SendResult;

interface WhatsAppProvider
{
    public function send(string $to, string $message): SendResult;
}
