<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\BusinessRole;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Staff;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\WorkingHour;
use App\Support\Tenancy;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CoreFlowsTest extends TestCase
{
    use RefreshDatabase;

    private function makeBusiness(string $slug, array $planOverrides = [], array $businessOverrides = []): array
    {
        $plan = SubscriptionPlan::create(array_merge([
            'name' => 'Test Plan '.$slug,
            'slug' => 'plan-'.$slug,
            'price_monthly' => 100,
            'price_yearly' => 1000,
            'max_staff' => 10,
            'max_customers' => 0,
            'max_appointments_per_month' => 0,
            'whatsapp_quota_monthly' => 0,
        ], $planOverrides));

        $business = new Business(array_merge([
            'name' => 'Isletme '.$slug,
            'slug' => $slug,
        ], $businessOverrides));
        $business->subscription_plan_id = $plan->id;
        $business->trial_ends_at = $businessOverrides['trial_ends_at'] ?? now()->addDays(10);
        $business->save();

        // fresh(): is_active gibi DB varsayilanlari modele yuklensin
        $owner = User::create([
            'name' => 'Owner '.$slug,
            'email' => "owner-{$slug}@test.com",
            'password' => Hash::make('password'),
        ])->fresh();
        $business->users()->attach($owner->id, ['role' => BusinessRole::Owner->value]);

        // Haftanin her gunu 09:00-19:00 acik
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

        $staff = new Staff(['name' => 'Personel '.$slug]);
        $staff->business_id = $business->id;
        $staff->save();

        $service = new Service(['name' => 'Hizmet '.$slug, 'duration_minutes' => 30, 'price' => 100]);
        $service->business_id = $business->id;
        $service->save();
        $service->staff()->attach($staff->id);

        return [$business, $owner, $staff, $service];
    }

    private function makeStaffUser(Business $business, string $email): array
    {
        $staffUser = User::create([
            'name' => 'Personel Kullanici',
            'email' => $email,
            'password' => Hash::make('password'),
        ])->fresh();
        $business->users()->attach($staffUser->id, ['role' => BusinessRole::Staff->value]);

        $ownStaff = new Staff(['name' => 'Panelli Personel']);
        $ownStaff->business_id = $business->id;
        $ownStaff->user_id = $staffUser->id;
        $ownStaff->save();

        return [$staffUser, $ownStaff];
    }

    private function makeAppointment(Business $business, Staff $staff, Service $service, \DateTimeInterface $startsAt): Appointment
    {
        $customer = new Customer(['name' => 'Musteri', 'phone' => '+9053300'.rand(10000, 99999)]);
        $customer->business_id = $business->id;
        $customer->save();

        $appointment = new Appointment([
            'customer_id' => $customer->id,
            'staff_id' => $staff->id,
            'service_id' => $service->id,
            'starts_at' => $startsAt,
            'ends_at' => Carbon::instance($startsAt)->addMinutes(30),
            'price' => 100,
        ]);
        $appointment->business_id = $business->id;
        $appointment->status = AppointmentStatus::Confirmed;
        $appointment->save();

        return $appointment;
    }

    protected function tearDown(): void
    {
        Tenancy::forget();
        parent::tearDown();
    }

    // Tenant izolasyonu ------------------------------------------------

    public function test_user_cannot_access_another_businesses_panel(): void
    {
        [, $ownerA] = $this->makeBusiness('salon-a');
        [$businessB] = $this->makeBusiness('salon-b');

        $this->actingAs($ownerA)
            ->get("/{$businessB->slug}/panel")
            ->assertForbidden();
    }

    public function test_cross_tenant_appointment_is_not_found(): void
    {
        [$businessA, $ownerA] = $this->makeBusiness('salon-c');
        [$businessB, , $staffB, $serviceB] = $this->makeBusiness('salon-d');

        $foreign = $this->makeAppointment($businessB, $staffB, $serviceB, now()->addDay()->setTime(10, 0));

        $this->actingAs($ownerA)
            ->get("/{$businessA->slug}/panel/randevular/{$foreign->id}")
            ->assertNotFound();
    }

    // Cifte rezervasyon korumasi ---------------------------------------

    public function test_overlapping_appointment_is_rejected(): void
    {
        [$business, $owner, $staff, $service] = $this->makeBusiness('salon-e');

        $slot = now()->addDay()->setTime(10, 0);
        $this->makeAppointment($business, $staff, $service, $slot);

        Tenancy::forget();

        $this->actingAs($owner)->postJson("/{$business->slug}/panel/randevular", [
            'customer_name' => 'Yeni Musteri',
            'customer_phone' => '+905331112299',
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'starts_at' => $slot->format('Y-m-d H:i:s'),
        ])->assertUnprocessable()->assertJsonValidationErrors(['starts_at']);
    }

    // Plan limitleri ----------------------------------------------------

    public function test_monthly_appointment_limit_is_enforced(): void
    {
        [$business, $owner, $staff, $service] = $this->makeBusiness('salon-f', [
            'max_appointments_per_month' => 1,
        ]);

        $this->makeAppointment($business, $staff, $service, now()->addDay()->setTime(10, 0));

        Tenancy::forget();

        $this->actingAs($owner)->postJson("/{$business->slug}/panel/randevular", [
            'customer_name' => 'Limit Musterisi',
            'customer_phone' => '+905331112288',
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'starts_at' => now()->addDay()->setTime(14, 0)->format('Y-m-d H:i:s'),
        ])->assertUnprocessable()->assertJsonValidationErrors(['starts_at']);
    }

    // Personel rol kisiti -------------------------------------------------

    public function test_staff_only_sees_own_calendar_events(): void
    {
        [$business, , $staffA, $service] = $this->makeBusiness('salon-g');
        [$staffUser, $ownStaff] = $this->makeStaffUser($business, 'staff-g@test.com');

        $this->makeAppointment($business, $staffA, $service, now()->addDay()->setTime(10, 0));
        $own = $this->makeAppointment($business, $ownStaff, $service, now()->addDay()->setTime(11, 0));

        Tenancy::forget();

        $response = $this->actingAs($staffUser)->getJson(
            "/{$business->slug}/panel/takvim/olaylar?start=".now()->toDateString().'&end='.now()->addDays(3)->toDateString()
        );

        $response->assertOk();

        $ids = collect($response->json())
            ->where('extendedProps.type', 'appointment')
            ->pluck('appointmentId');

        $this->assertTrue($ids->contains($own->id));
        $this->assertCount(1, $ids, 'Personel yalnizca kendi randevusunu gormeli');
    }

    public function test_staff_cannot_modify_other_staffs_appointment(): void
    {
        [$business, , $staffA, $service] = $this->makeBusiness('salon-h');
        [$staffUser] = $this->makeStaffUser($business, 'staff-h@test.com');

        $foreign = $this->makeAppointment($business, $staffA, $service, now()->addDay()->setTime(10, 0));

        Tenancy::forget();

        $this->actingAs($staffUser)
            ->patchJson("/{$business->slug}/panel/randevular/{$foreign->id}/durum", ['status' => 'completed'])
            ->assertForbidden();
    }

    // Abonelik kilidi -----------------------------------------------------

    public function test_expired_business_manager_is_redirected_to_subscription(): void
    {
        [$business, $owner] = $this->makeBusiness('salon-i');

        $business->forceFill(['trial_ends_at' => now()->subDay(), 'plan_expires_at' => null])->save();

        $this->actingAs($owner)
            ->get("/{$business->slug}/panel")
            ->assertRedirect("/{$business->slug}/panel/abonelik");
    }

    public function test_expired_business_booking_page_shows_unavailable(): void
    {
        [$business] = $this->makeBusiness('salon-j');

        $business->forceFill(['trial_ends_at' => now()->subDay(), 'plan_expires_at' => null])->save();

        $this->get("/{$business->slug}")
            ->assertOk()
            ->assertSee('online randevu kabul etmiyor');
    }

    // Online rezervasyon ---------------------------------------------------

    public function test_online_booking_creates_appointment_and_blocks_slot(): void
    {
        [$business, , $staff, $service] = $this->makeBusiness('salon-k');

        $date = now()->addDays(2)->toDateString();

        Tenancy::forget();

        $slots = $this->getJson("/{$business->slug}/uygun-saatler?service_id={$service->id}&staff_id={$staff->id}&date={$date}")
            ->assertOk()
            ->json('slots');

        $this->assertNotEmpty($slots);
        $time = $slots[0];

        $this->post("/{$business->slug}/randevu", [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'date' => $date,
            'time' => $time,
            'customer_name' => 'Online Musteri',
            'customer_phone' => '+905332223355',
            'kvkk' => '1',
        ])->assertRedirect();

        Tenancy::forget();

        $this->assertDatabaseHas('appointments', [
            'business_id' => $business->id,
            'staff_id' => $staff->id,
        ]);

        $slotsAfter = $this->getJson("/{$business->slug}/uygun-saatler?service_id={$service->id}&staff_id={$staff->id}&date={$date}")
            ->json('slots');

        $this->assertNotContains($time, $slotsAfter, 'Alinan slot listeden dusmeli');
    }
}
