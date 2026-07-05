<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sector')->nullable()->comment('berber, kuafor, guzellik, dis, spor vb.');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->text('description')->nullable();
            $table->string('instagram')->nullable();
            $table->string('website')->nullable();
            $table->string('timezone', 64)->default('Europe/Nicosia');
            $table->string('currency', 3)->default('TRY');

            // Rezervasyon ayarlari
            $table->unsignedSmallInteger('slot_interval_minutes')->default(15);
            $table->unsignedSmallInteger('min_notice_minutes')->default(60)->comment('online randevu icin min. on sure');
            $table->unsignedSmallInteger('max_advance_days')->default(60)->comment('en fazla kac gun sonrasina randevu');
            $table->boolean('online_booking_enabled')->default(true);
            $table->boolean('auto_confirm_online')->default(false)->comment('online randevu otomatik onaylansin mi');

            // Sadakat ayarlari
            $table->boolean('loyalty_enabled')->default(true);
            $table->unsignedSmallInteger('loyalty_points_per_visit')->default(10);
            $table->unsignedSmallInteger('loyalty_redeem_threshold')->default(100);
            $table->string('loyalty_reward_description')->nullable();

            // Bildirim ayarlari
            $table->boolean('whatsapp_enabled')->default(false);
            $table->string('whatsapp_provider', 30)->default('meta')->comment('meta, twilio, log');
            $table->text('whatsapp_api_key')->nullable()->comment('sifrelenmis');
            $table->string('whatsapp_phone_number_id')->nullable();
            $table->boolean('email_notifications_enabled')->default(true);
            $table->unsignedSmallInteger('reminder_hours_before')->default(24);

            // Abonelik durumu
            $table->foreignId('subscription_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
