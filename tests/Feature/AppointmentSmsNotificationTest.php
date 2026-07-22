<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\BusinessRole;
use App\Jobs\SendSmsMessage;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Staff;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\WorkingHour;
use App\Services\Booking\BookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AppointmentSmsNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function seedBusiness(bool $smsEnabled, bool $autoConfirmOnline): array
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Plan',
            'slug' => 'plan-sms',
            'price_monthly' => 100,
            'price_yearly' => 1000,
            'whatsapp_quota_monthly' => 100,
            'is_active' => true,
        ]);

        $business = Business::create([
            'name' => 'SMS Salon',
            'slug' => 'sms-salon',
            'online_booking_enabled' => true,
            'auto_confirm_online' => $autoConfirmOnline,
            'whatsapp_enabled' => false,
            'sms_enabled' => $smsEnabled,
            'trial_ends_at' => now()->addMonth(),
        ]);
        $business->forceFill(['subscription_plan_id' => $plan->id])->save();

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-sms@test.com',
            'password' => Hash::make('password'),
        ]);
        $business->users()->attach($owner->id, ['role' => BusinessRole::Owner->value]);

        foreach (range(0, 6) as $dow) {
            $hour = new WorkingHour([
                'day_of_week' => $dow,
                'start_time' => '09:00',
                'end_time' => '19:00',
                'is_closed' => false,
            ]);
            $hour->business_id = $business->id;
            $hour->save();
        }

        $staff = new Staff(['name' => 'Ali']);
        $staff->business_id = $business->id;
        $staff->save();

        $service = new Service([
            'name' => 'Tiras',
            'duration_minutes' => 30,
            'price' => 200,
        ]);
        $service->business_id = $business->id;
        $service->save();
        $service->staff()->attach($staff->id);

        $customer = new Customer([
            'name' => 'Musteri',
            'phone' => '+905331234567',
        ]);
        $customer->business_id = $business->id;
        $customer->save();

        return [$business, $owner, $staff, $service, $customer];
    }

    public function test_sms_dispatched_when_appointment_created_with_sms_enabled(): void
    {
        Bus::fake();

        [$business, , $staff, $service, $customer] = $this->seedBusiness(smsEnabled: true, autoConfirmOnline: true);

        app(BookingService::class)->create($business, [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'starts_at' => now()->addDay()->setTime(10, 0),
        ], source: 'panel');

        Bus::assertDispatched(SendSmsMessage::class, fn (SendSmsMessage $job) => $job->businessId === $business->id
            && $job->messageType === 'appointment_confirmation');
    }

    public function test_sms_not_dispatched_when_sms_disabled(): void
    {
        Bus::fake();

        [$business, , $staff, $service, $customer] = $this->seedBusiness(smsEnabled: false, autoConfirmOnline: true);

        app(BookingService::class)->create($business, [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'starts_at' => now()->addDay()->setTime(10, 0),
        ], source: 'panel');

        Bus::assertNotDispatched(SendSmsMessage::class);
    }

    public function test_sms_dispatched_when_online_pending_appointment_is_confirmed(): void
    {
        Bus::fake();

        [$business, , $staff, $service, $customer] = $this->seedBusiness(smsEnabled: true, autoConfirmOnline: false);

        $appointment = app(BookingService::class)->create($business, [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'starts_at' => now()->addDay()->setTime(11, 0),
        ], source: 'online', notify: false);

        $this->assertSame(AppointmentStatus::Pending, $appointment->status);

        app(BookingService::class)->updateStatus($appointment, AppointmentStatus::Confirmed);

        Bus::assertDispatched(SendSmsMessage::class, fn (SendSmsMessage $job) => $job->businessId === $business->id
            && $job->messageType === 'appointment_confirmation'
            && str_contains($job->message, 'Randevunuz onaylandı'));
    }

    public function test_online_booking_dispatches_sms_on_create_when_enabled(): void
    {
        Bus::fake();

        [$business, , $staff, $service, $customer] = $this->seedBusiness(smsEnabled: true, autoConfirmOnline: false);

        app(BookingService::class)->create($business, [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'starts_at' => now()->addDay()->setTime(10, 0),
        ], source: 'online');

        Bus::assertDispatched(SendSmsMessage::class, fn (SendSmsMessage $job) => $job->businessId === $business->id
            && str_contains($job->message, 'Randevu talebiniz alındı'));
    }
}
