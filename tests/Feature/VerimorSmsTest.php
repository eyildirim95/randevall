<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\MessageLog;
use App\Models\SubscriptionPlan;
use App\Services\Messaging\SmsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VerimorSmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_verimor_sms_provider_sends_message(): void
    {
        Http::fake([
            'sms.verimor.com.tr/*' => Http::response('12345678', 200),
        ]);

        \App\Models\SystemSetting::set('sms_provider', 'verimor');
        \App\Models\SystemSetting::set('sms_username', '908501234567');
        SmsManager::storePassword('secret');
        \App\Models\SystemSetting::set('sms_source_addr', 'RANDEVALL');

        $plan = SubscriptionPlan::create([
            'name' => 'Plan',
            'slug' => 'plan',
            'price_monthly' => 100,
            'price_yearly' => 1000,
            'is_active' => true,
        ]);

        $business = Business::create([
            'name' => 'Test',
            'slug' => 'test',
            'sms_enabled' => true,
        ]);
        $business->forceFill(['subscription_plan_id' => $plan->id])->save();

        $result = app(SmsManager::class)->send($business, '+905331234567', 'Test mesaji', 'system');

        $this->assertTrue($result->success);
        $this->assertDatabaseHas('message_logs', [
            'business_id' => $business->id,
            'channel' => 'sms',
            'status' => 'sent',
        ]);

        Http::assertSent(fn ($request) => $request->url() === 'https://sms.verimor.com.tr/v2/send.json'
            && $request['username'] === '908501234567'
            && $request['source_addr'] === 'RANDEVALL'
            && $request['messages'][0]['dest'] === '905331234567');
    }

    public function test_sms_disabled_returns_failure_without_sending(): void
    {
        Http::fake();

        $business = Business::create([
            'name' => 'Test',
            'slug' => 'test-off',
            'sms_enabled' => false,
        ]);

        $result = app(SmsManager::class)->send($business, '05331234567', 'Test', 'manual');

        $this->assertFalse($result->success);
        $this->assertSame('sms_disabled', $result->error);
        Http::assertNothingSent();
    }
}
