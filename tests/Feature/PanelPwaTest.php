<?php

namespace Tests\Feature;

use App\Enums\BusinessRole;
use App\Models\Business;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PanelPwaTest extends TestCase
{
    use RefreshDatabase;

    private function makeBusinessWithOwner(): array
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Plan',
            'slug' => 'plan-pwa',
            'price_monthly' => 100,
            'price_yearly' => 1000,
            'is_active' => true,
        ]);

        $business = Business::create(['name' => 'PWA Salon', 'slug' => 'pwa-salon']);
        $business->forceFill([
            'subscription_plan_id' => $plan->id,
            'trial_ends_at' => now()->addMonth(),
        ])->save();

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-pwa@test.com',
            'password' => Hash::make('password'),
        ])->fresh();
        $owner->forceFill(['email_verified_at' => now(), 'is_active' => true])->save();
        $business->users()->attach($owner->id, ['role' => BusinessRole::Owner->value]);

        return [$business, $owner];
    }

    public function test_panel_manifest_is_available_for_tenant(): void
    {
        [$business, $owner] = $this->makeBusinessWithOwner();

        $this->actingAs($owner)
            ->get("/{$business->slug}/panel/manifest.webmanifest")
            ->assertOk()
            ->assertHeader('content-type', 'application/manifest+json; charset=UTF-8')
            ->assertJson([
                'short_name' => 'PWA Salon',
                'display' => 'standalone',
                'lang' => 'tr',
            ])
            ->assertJsonStructure(['name', 'start_url', 'scope', 'icons']);
    }

    public function test_panel_service_worker_is_served_as_javascript(): void
    {
        [$business, $owner] = $this->makeBusinessWithOwner();

        $response = $this->actingAs($owner)
            ->get("/{$business->slug}/panel/sw.js");

        $response->assertOk()
            ->assertHeader('content-type', 'application/javascript; charset=UTF-8');

        $this->assertStringContainsString('addEventListener', $response->getContent());
    }

    public function test_panel_page_includes_pwa_meta_and_mobile_nav(): void
    {
        [$business, $owner] = $this->makeBusinessWithOwner();

        $this->actingAs($owner)
            ->get("/{$business->slug}/panel")
            ->assertOk()
            ->assertSee('manifest.webmanifest', false)
            ->assertSee('panel-mobile-nav', false)
            ->assertSee('panel-pwa.js', false);
    }
}
