<?php

namespace Tests\Feature;

use App\Enums\BusinessRole;
use App\Models\Business;
use App\Models\PanelNotification;
use App\Models\Service;
use App\Models\Staff;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\WorkingHour;
use App\Support\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationAndFeedsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Tenancy::forget();
        parent::tearDown();
    }

    public function test_self_service_registration_creates_business_and_logs_in(): void
    {
        SubscriptionPlan::create([
            'name' => 'Pro', 'slug' => 'pro', 'price_monthly' => 100, 'price_yearly' => 1000,
            'max_staff' => 5, 'max_customers' => 0, 'max_appointments_per_month' => 0,
            'whatsapp_quota_monthly' => 0, 'trial_days' => 14, 'is_featured' => true,
        ]);

        $response = $this->post('/kayit', [
            'business_name' => 'Yeni Berber',
            'slug' => 'yeni-berber',
            'sector' => 'Berber',
            'name' => 'Kayit Testi',
            'email' => 'kayit@test.com',
            'phone' => '+905331234567',
            'password' => 'sifre1234',
            'password_confirmation' => 'sifre1234',
            'terms' => '1',
        ]);

        $response->assertRedirect('/yeni-berber/panel');
        $this->assertAuthenticated();

        $business = Business::query()->where('slug', 'yeni-berber')->first();
        $this->assertNotNull($business);
        $this->assertTrue($business->onTrial());
        $this->assertSame(7, $business->workingHours()->count());
        $this->assertSame(BusinessRole::Owner->value, User::query()->where('email', 'kayit@test.com')->first()->roleIn($business));
    }

    public function test_reserved_slug_is_rejected(): void
    {
        $this->post('/kayit', [
            'business_name' => 'Kotu Niyet',
            'slug' => 'admin',
            'name' => 'X',
            'email' => 'x@test.com',
            'phone' => '+905331234567',
            'password' => 'sifre1234',
            'password_confirmation' => 'sifre1234',
            'terms' => '1',
        ])->assertSessionHasErrors(['slug']);
    }

    public function test_ics_feed_returns_calendar_with_appointments(): void
    {
        $business = new Business(['name' => 'Feed Salon', 'slug' => 'feed-salon']);
        $business->trial_ends_at = now()->addDays(10);
        $business->save();

        $staff = new Staff(['name' => 'Feed Personel']);
        $staff->business_id = $business->id;
        $staff->save();

        $service = new Service(['name' => 'Kesim', 'duration_minutes' => 30, 'price' => 100]);
        $service->business_id = $business->id;
        $service->save();

        $customer = new \App\Models\Customer(['name' => 'Feed Musteri', 'phone' => '+905330001122']);
        $customer->business_id = $business->id;
        $customer->save();

        $appointment = new \App\Models\Appointment([
            'customer_id' => $customer->id,
            'staff_id' => $staff->id,
            'service_id' => $service->id,
            'starts_at' => now()->addDay()->setTime(10, 0),
            'ends_at' => now()->addDay()->setTime(10, 30),
            'price' => 100,
        ]);
        $appointment->business_id = $business->id;
        $appointment->status = \App\Enums\AppointmentStatus::Confirmed;
        $appointment->save();

        $token = $staff->icsToken();

        $response = $this->get("/takvim-feed/{$token}.ics");

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/calendar; charset=UTF-8');

        $body = $response->getContent();
        $this->assertStringContainsString('BEGIN:VCALENDAR', $body);
        $this->assertStringContainsString('Kesim', $body);
        $this->assertStringContainsString('Feed Musteri', $body);
    }

    public function test_online_booking_creates_panel_notification(): void
    {
        $business = new Business(['name' => 'Bildirim Salon', 'slug' => 'bildirim-salon']);
        $business->trial_ends_at = now()->addDays(10);
        $business->save();

        foreach (range(0, 6) as $dow) {
            $hour = new WorkingHour(['day_of_week' => $dow, 'start_time' => '09:00', 'end_time' => '19:00', 'is_closed' => false]);
            $hour->business_id = $business->id;
            $hour->save();
        }

        $staff = new Staff(['name' => 'Personel']);
        $staff->business_id = $business->id;
        $staff->save();

        $service = new Service(['name' => 'Hizmet', 'duration_minutes' => 30, 'price' => 100]);
        $service->business_id = $business->id;
        $service->save();
        $service->staff()->attach($staff->id);

        Tenancy::forget();

        $this->post("/{$business->slug}/randevu", [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => now()->addDays(2)->toDateString(),
            'time' => '10:00',
            'customer_name' => 'Bildirim Musterisi',
            'customer_phone' => '+905332221100',
            'kvkk' => '1',
        ])->assertRedirect();

        Tenancy::forget();

        $this->assertDatabaseHas('panel_notifications', [
            'business_id' => $business->id,
            'type' => 'online_booking',
        ]);
    }
}
