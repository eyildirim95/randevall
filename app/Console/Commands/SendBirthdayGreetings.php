<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Customer;
use App\Services\Messaging\WhatsAppManager;
use Illuminate\Console\Command;

class SendBirthdayGreetings extends Command
{
    protected $signature = 'reserup:send-birthday-greetings';

    protected $description = 'Dogum gunu olan musterilere kutlama mesaji gonderir';

    public function handle(WhatsAppManager $whatsapp): int
    {
        $businesses = Business::query()
            ->where('is_active', true)
            ->whereNull('suspended_at')
            ->where('whatsapp_enabled', true)
            ->where('birthday_greeting_enabled', true)
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
                // Ayni yil icinde tekrar gonderilmesin
                $alreadySent = \App\Models\MessageLog::query()
                    ->where('business_id', $business->id)
                    ->where('message_type', 'birthday')
                    ->where('recipient', \App\Services\Messaging\PhoneNumber::e164($customer->phone))
                    ->whereYear('created_at', today()->year)
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $result = $whatsapp->send(
                    $business,
                    $customer->phone,
                    sprintf(
                        "İyi ki doğdun %s! 🎂🎉\n\n%s ailesi olarak nice mutlu, sağlıklı yıllar dileriz.",
                        $customer->name,
                        $business->name,
                    ),
                    'birthday',
                );

                if ($result->success) {
                    $total++;
                }
            }
        }

        $this->info("Gonderilen dogum gunu mesaji: {$total}");

        return self::SUCCESS;
    }
}
