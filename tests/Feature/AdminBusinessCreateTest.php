<?php

namespace Tests\Feature;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminBusinessCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_business_with_owner(): void
    {
        SubscriptionPlan::create([
            'name' => 'Başlangıç',
            'slug' => 'baslangic',
            'price_monthly' => 499,
            'price_yearly' => 4990,
            'currency' => 'TRY',
            'is_active' => true,
            'trial_days' => 0,
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@randevall.com',
            'password' => Hash::make('password'),
        ]);
        $admin->forceFill(['is_super_admin' => true, 'email_verified_at' => now()])->save();

        $response = $this->actingAs($admin)->post(route('admin.businesses.store'), [
            'business' => [
                'name' => 'Yeni Berber',
                'slug' => 'yeni-berber',
                'sector' => 'Berber',
                'phone' => '+90 533 111 22 33',
                'email' => 'info@yeni-berber.com',
                'address' => 'Test Sokak',
                'city' => 'İstanbul',
            ],
            'plan_id' => SubscriptionPlan::first()->id,
            'owner' => [
                'name' => 'Sahip Kullanıcı',
                'email' => 'sahip@yeni-berber.com',
                'phone' => '+90 533 444 55 66',
                'password' => 'sifre12345',
            ],
        ]);

        $response->assertRedirect(route('admin.businesses.show', 'yeni-berber'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('businesses', ['slug' => 'yeni-berber']);
        $this->assertDatabaseHas('users', ['email' => 'sahip@yeni-berber.com']);
        $this->assertSame(7, WorkingHour::withoutTenantScope()->whereHas('business', fn ($q) => $q->where('slug', 'yeni-berber'))->count());

        $show = $this->actingAs($admin)->get(route('admin.businesses.show', 'yeni-berber'));
        $show->assertOk();
    }
}
