<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Customer;
use App\Services\Messaging\PhoneNumber;
use App\Services\Messaging\SmsManager;
use App\Services\Messaging\WhatsAppManager;
use Illuminate\Console\Command;

class SendBirthdayGreetings extends Command
{
    protected $signature = 'reserup:send-birthday-greetings';

    protected $description = 'Dogum gunu olan musterilere kutlama mesaji gonderir';

    public function handle(WhatsAppManager $whatsapp, SmsManager $sms): int
    {
        $businesses = Business::query()
            ->where('is_active', true)
            ->whereNull('suspended_at')
            ->where('birthday_greeting_enabled', true)
            ->where(fn ($q) => $q->where('whatsapp_enabled', true)->orWhere('sms_enabled', true))
            ->get();

        $total = 0;

        foreach ($businesses as $business) {
            $customers = Customer::withoutTenantScope()
                ->where('business_id', $business->id)
                ->where('is_blacklisted', false)
                ->whereNotNull('birthday')
                ->whereMonth('birthday', today()->month)
                ->whereDay('birthday', today()->day)
                ->get();

            foreach ($customers as $customer) {
                $recipient = PhoneNumber::e164($customer->phone);

                $alreadySent = \App\Models\MessageLog::query()
                    ->where('business_id', $business->id)
                    ->where('message_type', 'birthday')
                    ->where('recipient', $recipient)
                    ->whereYear('created_at', today()->year)
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $message = sprintf(
                    "İyi ki doğdun %s! 🎂🎉\n\n%s ailesi olarak nice mutlu, sağlıklı yıllar dileriz.",
                    $customer->name,
                    $business->name,
                );

                $sent = false;

                if ($business->whatsapp_enabled) {
                    $sent = $whatsapp->send($business, $customer->phone, $message, 'birthday')->success || $sent;
                }

                if ($business->sms_enabled) {
                    $sent = $sms->send($business, $customer->phone, $message, 'birthday')->success || $sent;
                }

                if ($sent) {
                    $total++;
                }
            }
        }

        $this->info("Gonderilen dogum gunu mesaji: {$total}");

        return self::SUCCESS;
    }
}
