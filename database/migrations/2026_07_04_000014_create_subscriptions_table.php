<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->string('period', 10)->default('monthly')->comment('monthly, yearly');
            $table->decimal('price', 10, 2)->comment('baglanma anindaki fiyat');
            $table->string('currency', 3)->default('TRY');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status', 20)->default('active')->comment('trial, active, past_due, cancelled, expired');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 30)->default('manual')->comment('iyzico, paytr, stripe, manual');
            $table->string('provider_ref')->nullable()->comment('saglayici islem no');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('TRY');
            $table->string('status', 20)->default('pending')->comment('pending, paid, failed, refunded');
            $table->timestamp('paid_at')->nullable();
            $table->json('payload')->nullable()->comment('saglayici ham cevabi');
            $table->uuid('token')->unique()->comment('odeme sayfasi guvenlik tokeni');
            $table->timestamps();

            $table->index(['business_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscriptions');
    }
};
