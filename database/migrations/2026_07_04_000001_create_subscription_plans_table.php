<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->string('currency', 3)->default('TRY');
            $table->unsignedSmallInteger('max_staff')->default(1);
            $table->unsignedInteger('max_customers')->default(0)->comment('0 = sinirsiz');
            $table->unsignedInteger('max_appointments_per_month')->default(0)->comment('0 = sinirsiz');
            $table->unsignedInteger('whatsapp_quota_monthly')->default(0)->comment('0 = sinirsiz');
            $table->json('features')->nullable();
            $table->unsignedTinyInteger('trial_days')->default(14);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
