<?php

namespace App\Jobs;

use App\Models\Business;
use App\Services\Messaging\SmsManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendSmsMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly int $businessId,
        public readonly string $to,
        public readonly string $message,
        public readonly string $messageType = 'manual',
        public readonly ?int $appointmentId = null,
    ) {}

    public function handle(SmsManager $manager): void
    {
        $business = Business::find($this->businessId);

        if (! $business) {
            return;
        }

        $manager->send($business, $this->to, $this->message, $this->messageType, $this->appointmentId);
    }
}
