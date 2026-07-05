<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Degerlendirme + tekrarlayan seri alanlari
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedTinyInteger('rating')->nullable()->after('cancellation_reason')->comment('1-5 musteri puani');
            $table->string('rating_comment', 500)->nullable()->after('rating');
            $table->timestamp('rated_at')->nullable()->after('rating_comment');
            $table->uuid('series_id')->nullable()->after('rated_at')->comment('tekrarlayan randevu grubu');
            $table->index('series_id');
        });

        // Dogum gunu otomasyonu tercihi
        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('birthday_greeting_enabled')->default(false)->after('loyalty_reward_description');
        });

        // Bekleme listesi
        Schema::create('waiting_list_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name', 120);
            $table->string('customer_phone', 30);
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->date('preferred_date');
            $table->string('status', 15)->default('waiting')->comment('waiting, notified, removed');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'preferred_date', 'status']);
        });

        // Kampanyalar
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->text('message');
            $table->string('audience', 20)->default('all')->comment('all, loyal, recent');
            $table->string('status', 15)->default('draft')->comment('draft, sending, sent');
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('waiting_list_entries');

        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('birthday_greeting_enabled');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['series_id']);
            $table->dropColumn(['rating', 'rating_comment', 'rated_at', 'series_id']);
        });
    }
};
