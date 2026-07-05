<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panel_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40)->comment('online_booking, customer_cancelled, waitlist_joined, ticket_replied');
            $table->string('title', 190);
            $table->string('url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'read_at']);
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->uuid('ics_token')->nullable()->unique()->after('photo_path')->comment('takvim beslemesi gizli anahtari');
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn('ics_token');
        });

        Schema::dropIfExists('panel_notifications');
    }
};
