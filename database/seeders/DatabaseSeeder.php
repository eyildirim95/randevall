<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\BusinessRole;
use App\Enums\TransactionType;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Note;
use App\Models\Service;
use App\Models\Staff;
use App\Models\SubscriptionPlan;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Super admin ─────────────────────────────────────────
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@bookibris.com'],
            ['name' => 'BooKıbrıs Admin', 'password' => Hash::make('admin123!')],
        );
        $admin->forceFill(['is_super_admin' => true, 'email_verified_at' => now()])->save();

        // ── Planlar ─────────────────────────────────────────────
        $plans = [
            [
                'name' => 'Başlangıç', 'slug' => 'baslangic',
                'description' => 'Tek kişilik işletmeler için',
                'price_monthly' => 499, 'price_yearly' => 4990,
                'max_staff' => 1, 'max_customers' => 0, 'max_appointments_per_month' => 200,
                'whatsapp_quota_monthly' => 200, 'trial_days' => 14, 'sort_order' => 1,
                'features' => ['Online randevu linki', 'WhatsApp bildirimleri', 'Gelir-gider takibi'],
            ],
            [
                'name' => 'Profesyonel', 'slug' => 'profesyonel',
                'description' => 'Büyüyen ekipler için', 'is_featured' => true,
                'price_monthly' => 899, 'price_yearly' => 8990,
                'max_staff' => 5, 'max_customers' => 0, 'max_appointments_per_month' => 0,
                'whatsapp_quota_monthly' => 1000, 'trial_days' => 14, 'sort_order' => 2,
                'features' => ['Sınırsız randevu', 'Sadık müşteri sistemi', 'Detaylı raporlar', 'Öncelikli destek'],
            ],
            [
                'name' => 'İşletme', 'slug' => 'isletme',
                'description' => 'Çok personelli salonlar için',
                'price_monthly' => 1499, 'price_yearly' => 14990,
                'max_staff' => 0, 'max_customers' => 0, 'max_appointments_per_month' => 0,
                'whatsapp_quota_monthly' => 0, 'trial_days' => 14, 'sort_order' => 3,
                'features' => ['Sınırsız her şey', 'Özel kurulum desteği', 'Telefon desteği'],
            ],
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::query()->updateOrCreate(['slug' => $planData['slug']], $planData);
        }

        // ── Sistem ayarlari ─────────────────────────────────────
        SystemSetting::set('notification_email', 'admin@bookibris.com');

        // Merkezi WhatsApp: gelistirmede log saglayicisi
        if (! SystemSetting::get('whatsapp_provider')) {
            SystemSetting::set('whatsapp_provider', 'log');
        }

        // ── Demo isletme ────────────────────────────────────────
        if (Business::query()->where('slug', 'demo-berber')->exists()) {
            return;
        }

        $business = new Business([
            'name' => 'Demo Berber',
            'slug' => 'demo-berber',
            'sector' => 'Berber',
            'phone' => '+90 533 000 00 00',
            'email' => 'demo@bookibris.com',
            'address' => 'Atatürk Caddesi No: 1',
            'city' => 'Girne',
            'description' => 'Girne\'nin merkezinde modern erkek kuaförü.',
            'whatsapp_enabled' => true,
            'auto_confirm_online' => false,
            'loyalty_enabled' => true,
            'loyalty_points_per_visit' => 10,
            'loyalty_redeem_threshold' => 100,
            'loyalty_reward_description' => 'Ücretsiz saç tıraşı',
        ]);
        $business->subscription_plan_id = SubscriptionPlan::query()->where('slug', 'profesyonel')->value('id');
        $business->trial_ends_at = now()->addDays(14);
        $business->save();

        $owner = User::query()->firstOrCreate(
            ['email' => 'demo@bookibris.com'],
            ['name' => 'Demo İşletmeci', 'phone' => '+90 533 000 00 00', 'password' => Hash::make('demo123!')],
        );
        $owner->forceFill(['email_verified_at' => now()])->save();
        $business->users()->syncWithoutDetaching([$owner->id => ['role' => BusinessRole::Owner->value]]);

        // Calisma saatleri: Pzt-Cmt 09:00-19:00 (13-14 mola), Pazar kapali
        foreach (range(0, 6) as $dow) {
            WorkingHour::query()->create([
                'business_id' => $business->id,
                'day_of_week' => $dow,
                'is_closed' => $dow === 0,
                'start_time' => $dow === 0 ? null : '09:00',
                'end_time' => $dow === 0 ? null : '19:00',
                'break_start' => $dow === 0 ? null : '13:00',
                'break_end' => $dow === 0 ? null : '14:00',
            ]);
        }

        // Personel
        $staffMembers = collect([
            ['name' => 'Mehmet Usta', 'title' => 'Usta Berber', 'color' => '#7f56da'],
            ['name' => 'Ali Kalfa', 'title' => 'Berber', 'color' => '#22c55e'],
        ])->map(function (array $data) use ($business) {
            $staff = new Staff($data);
            $staff->business_id = $business->id;
            $staff->save();

            return $staff;
        });

        // Hizmetler
        $services = collect([
            ['name' => 'Saç Tıraşı', 'duration_minutes' => 30, 'price' => 350, 'color' => '#7f56da'],
            ['name' => 'Sakal Tıraşı', 'duration_minutes' => 20, 'price' => 200, 'color' => '#22c55e'],
            ['name' => 'Saç + Sakal', 'duration_minutes' => 45, 'price' => 500, 'color' => '#f59e0b'],
            ['name' => 'Cilt Bakımı', 'duration_minutes' => 30, 'price' => 400, 'color' => '#3b82f6'],
        ])->map(function (array $data) use ($business, $staffMembers) {
            $service = new Service($data);
            $service->business_id = $business->id;
            $service->save();
            $service->staff()->sync($staffMembers->pluck('id'));

            return $service;
        });

        // Musteriler
        $customers = collect([
            ['name' => 'Ahmet Yılmaz', 'phone' => '+905330000001', 'email' => 'ahmet@example.com'],
            ['name' => 'Hasan Kaya', 'phone' => '+905330000002'],
            ['name' => 'Murat Demir', 'phone' => '+905330000003', 'birthday' => today()->subYears(30)->toDateString()],
            ['name' => 'Kemal Şen', 'phone' => '+905330000004'],
            ['name' => 'Osman Ak', 'phone' => '+905330000005'],
        ])->map(function (array $data) use ($business) {
            $customer = new Customer($data);
            $customer->business_id = $business->id;
            $customer->kvkk_consent_at = now();
            $customer->save();

            return $customer;
        });

        // Gecmis + gelecek randevular
        $pastStatuses = [AppointmentStatus::Completed, AppointmentStatus::Completed, AppointmentStatus::Cancelled, AppointmentStatus::NoShow];

        foreach (range(1, 20) as $i) {
            $daysOffset = $i <= 12 ? -rand(1, 21) : rand(0, 7);
            $startsAt = today()->addDays($daysOffset)->setTime(rand(9, 17), [0, 30][rand(0, 1)]);

            // Pazar gunune denk gelmesin
            if ($startsAt->dayOfWeek === 0) {
                $startsAt->addDay();
            }

            $service = $services->random();
            $status = $daysOffset < 0
                ? $pastStatuses[array_rand($pastStatuses)]
                : (rand(0, 1) ? AppointmentStatus::Confirmed : AppointmentStatus::Pending);

            $appointment = new Appointment([
                'customer_id' => $customers->random()->id,
                'staff_id' => $staffMembers->random()->id,
                'service_id' => $service->id,
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addMinutes($service->duration_minutes),
                'price' => $service->price,
                'source' => rand(0, 1) ? 'panel' : 'online',
            ]);
            $appointment->business_id = $business->id;
            $appointment->status = $status;
            $appointment->save();

            if ($status === AppointmentStatus::Completed) {
                $transaction = new Transaction([
                    'type' => TransactionType::Income,
                    'category' => 'Hizmet',
                    'amount' => $service->price,
                    'description' => $service->name,
                    'transaction_date' => $startsAt->toDateString(),
                    'appointment_id' => $appointment->id,
                    'customer_id' => $appointment->customer_id,
                ]);
                $transaction->business_id = $business->id;
                $transaction->save();
            }
        }

        // Ornek giderler
        foreach ([
            ['category' => 'Kira', 'amount' => 15000, 'description' => 'Aylık dükkan kirası'],
            ['category' => 'Malzeme', 'amount' => 2500, 'description' => 'Tıraş köpüğü, jilet, havlu'],
            ['category' => 'Fatura', 'amount' => 1800, 'description' => 'Elektrik + su'],
        ] as $expenseData) {
            $transaction = new Transaction([
                ...$expenseData,
                'type' => TransactionType::Expense,
                'transaction_date' => today()->startOfMonth()->addDays(2)->toDateString(),
            ]);
            $transaction->business_id = $business->id;
            $transaction->save();
        }

        // Ornek not
        $note = new Note([
            'title' => 'Hoş geldiniz!',
            'content' => 'BooKıbrıs demo işletmesine hoş geldiniz. Bu panelden randevularınızı, müşterilerinizi ve gelir-giderinizi yönetebilirsiniz.',
            'color' => 'info',
            'is_pinned' => true,
        ]);
        $note->business_id = $business->id;
        $note->user_id = $owner->id;
        $note->save();
    }
}
