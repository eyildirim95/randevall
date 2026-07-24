<?php

namespace Tests\Feature;

use App\Enums\BusinessRole;
use App\Models\Business;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CalendarPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_page_loads_with_lookup_route(): void
    {
        $this->assertTrue(Route::has('panel.customers.lookup-phone'));

        $plan = SubscriptionPlan::create([
            'name' => 'Plan',
            'slug' => 'plan-cal',
            'price_monthly' => 100,
            'price_yearly' => 1000,
            'is_active' => true,
        ]);

        $business = Business::create(['name' => 'Salon', 'slug' => 'salon-cal']);
        $business->forceFill([
            'subscription_plan_id' => $plan->id,
            'trial_ends_at' => now()->addMonth(),
        ])->save();

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-cal@test.com',
            'password' => Hash::make('password'),
        ])->fresh();
        $owner->forceFill(['email_verified_at' => now(), 'is_active' => true])->save();
        $business->users()->attach($owner->id, ['role' => BusinessRole::Owner->value]);

        $this->actingAs($owner)
            ->get("/{$business->slug}/panel/takvim")
            ->assertOk()
            ->assertSee('panel-calendar', false);
    }
}
