<?php

namespace Tests\Feature;

use App\Enums\BusinessRole;
use App\Models\Business;
use App\Models\Customer;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerPhoneDedupTest extends TestCase
{
    use RefreshDatabase;

    private function makeBusinessWithOwner(): array
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Plan',
            'slug' => 'plan-dedup',
            'price_monthly' => 100,
            'price_yearly' => 1000,
            'is_active' => true,
        ]);

        $business = Business::create([
            'name' => 'Salon',
            'slug' => 'salon',
        ]);
        $business->forceFill([
            'subscription_plan_id' => $plan->id,
            'trial_ends_at' => now()->addMonth(),
        ])->save();

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-dedup@test.com',
            'password' => Hash::make('password'),
        ])->fresh();
        $owner->forceFill(['email_verified_at' => now(), 'is_active' => true])->save();
        $business->users()->attach($owner->id, ['role' => BusinessRole::Owner->value]);

        return [$business, $owner];
    }

    public function test_manual_customer_create_redirects_when_phone_exists(): void
    {
        [$business, $owner] = $this->makeBusinessWithOwner();

        $existing = new Customer([
            'name' => 'Mevcut Müşteri',
            'phone' => '+905331234567',
        ]);
        $existing->business_id = $business->id;
        $existing->save();

        $response = $this->actingAs($owner)->post("/{$business->slug}/panel/musteriler", [
            'name' => 'Başka İsim',
            'phone' => '0533 123 45 67',
        ]);

        $response->assertRedirect(route('panel.customers.show', [$business, $existing]));
        $this->assertSame(1, Customer::query()->count());
    }

    public function test_phone_lookup_finds_existing_customer(): void
    {
        [$business, $owner] = $this->makeBusinessWithOwner();

        $customer = new Customer([
            'name' => 'Ali Veli',
            'phone' => '+905339876543',
        ]);
        $customer->business_id = $business->id;
        $customer->save();

        $response = $this->actingAs($owner)->getJson("/{$business->slug}/panel/musteriler/telefon-ara?phone=".urlencode('0533 987 65 43'));

        $response->assertOk()
            ->assertJson([
                'found' => true,
                'customer' => [
                    'id' => $customer->id,
                    'name' => 'Ali Veli',
                ],
            ]);
    }
}
