<?php

namespace Tests\Feature;

use App\Enums\BusinessRole;
use App\Models\Business;
use App\Models\Customer;
use App\Models\CustomerRecord;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_add_customer_tracking_record(): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Plan',
            'slug' => 'plan-records',
            'price_monthly' => 100,
            'price_yearly' => 1000,
            'is_active' => true,
        ]);

        $business = Business::create([
            'name' => 'Klinik',
            'slug' => 'klinik',
        ]);
        $business->forceFill([
            'subscription_plan_id' => $plan->id,
            'trial_ends_at' => now()->addMonth(),
        ])->save();

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-records@test.com',
            'password' => Hash::make('password'),
        ])->fresh();
        $owner->forceFill(['email_verified_at' => now(), 'is_active' => true])->save();
        $business->users()->attach($owner->id, ['role' => BusinessRole::Owner->value]);

        $customer = new Customer(['name' => 'Ali Veli', 'phone' => '+905331112233']);
        $customer->business_id = $business->id;
        $customer->save();

        $response = $this->actingAs($owner)->post("/{$business->slug}/panel/musteriler/{$customer->id}/takip-notu", [
            'body' => '26 numaralı dişe kompozit dolgu uygulandı.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customer_records', [
            'customer_id' => $customer->id,
            'business_id' => $business->id,
            'created_by' => $owner->id,
            'body' => '26 numaralı dişe kompozit dolgu uygulandı.',
        ]);
    }

    public function test_manager_can_delete_customer_tracking_record(): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Plan',
            'slug' => 'plan-records-del',
            'price_monthly' => 100,
            'price_yearly' => 1000,
            'is_active' => true,
        ]);

        $business = Business::create([
            'name' => 'Okul',
            'slug' => 'okul',
        ]);
        $business->forceFill([
            'subscription_plan_id' => $plan->id,
            'trial_ends_at' => now()->addMonth(),
        ])->save();

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-del@test.com',
            'password' => Hash::make('password'),
        ])->fresh();
        $owner->forceFill(['email_verified_at' => now(), 'is_active' => true])->save();
        $business->users()->attach($owner->id, ['role' => BusinessRole::Owner->value]);

        $customer = new Customer(['name' => 'Ogrenci', 'phone' => '+905334445566']);
        $customer->business_id = $business->id;
        $customer->save();

        $record = new CustomerRecord([
            'body' => 'Deneme notu',
            'created_by' => $owner->id,
        ]);
        $record->business_id = $business->id;
        $record->customer_id = $customer->id;
        $record->save();

        $this->actingAs($owner)
            ->delete("/{$business->slug}/panel/musteriler/{$customer->id}/takip-notu/{$record->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('customer_records', ['id' => $record->id]);
    }
}
