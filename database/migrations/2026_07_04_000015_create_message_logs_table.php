<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->cascadeOnDelete()->comment('null = platform mesaji');
            $table->string('channel', 15)->comment('whatsapp, email');
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('message_type', 40)->default('manual')->comment('appointment_confirmation, appointment_reminder, appointment_cancelled, demo_request, system, manual');
            $table->string('status', 15)->default('queued')->comment('queued, sent, failed');
            $table->string('provider_response')->nullable();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'channel', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_logs');
    }
};
