<?php

namespace Tests\Feature;

use App\Enums\SubscriptionStatus;
use App\Models\Business;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ManualSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_grant_manual_subscription(): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Profesyonel',
            'slug' => 'profesyonel',
            'price_monthly' => 899,
            'price_yearly' => 8990,
            'currency' => 'TRY',
            'is_active' => true,
        ]);

        $business = Business::create([
            'name' => 'Test Berber',
            'slug' => 'test-berber',
            'trial_ends_at' => now()->subDay(),
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@randevall.com',
            'password' => Hash::make('password'),
        ]);
        $admin->forceFill(['is_super_admin' => true])->save();

        $response = $this->actingAs($admin)->post(route('admin.businesses.subscriptions.store', $business), [
            'plan_id' => $plan->id,
            'period' => 'monthly',
            'note' => 'Havale ile odendi',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $business->refresh();
        $subscription = Subscription::query()->where('business_id', $business->id)->first();

        $this->assertNotNull($subscription);
        $this->assertSame(SubscriptionStatus::Active, $subscription->status);
        $this->assertSame($plan->id, $business->subscription_plan_id);
        $this->assertNull($business->trial_ends_at);
        $this->assertTrue($business->plan_expires_at->isFuture());
        $this->assertDatabaseHas('payments', [
            'business_id' => $business->id,
            'subscription_id' => $subscription->id,
            'status' => 'paid',
            'provider' => 'manual',
        ]);
    }

    public function test_has_active_plan_falls_back_to_active_subscription(): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Profesyonel',
            'slug' => 'profesyonel-fallback',
            'price_monthly' => 899,
            'price_yearly' => 8990,
            'currency' => 'TRY',
            'is_active' => true,
        ]);

        $business = Business::create([
            'name' => 'Fallback Berber',
            'slug' => 'fallback-berber',
            'trial_ends_at' => now()->subDay(),
        ]);

        Subscription::create([
            'business_id' => $business->id,
            'subscription_plan_id' => $plan->id,
            'period' => 'monthly',
            'price' => 899,
            'currency' => 'TRY',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => SubscriptionStatus::Active,
        ]);

        $this->assertTrue($business->fresh()->hasActivePlan());
    }
}
