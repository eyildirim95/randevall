<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status', 20)->default('pending')->comment('pending, confirmed, completed, cancelled, no_show');
            $table->decimal('price', 10, 2)->default(0)->comment('hizmet fiyati anlik kopyasi');
            $table->text('notes')->nullable();
            $table->string('source', 10)->default('panel')->comment('panel, online');
            $table->string('cancellation_reason')->nullable();
            $table->uuid('public_token')->unique()->comment('musteri iptal/goruntuleme linki');
            $table->timestamp('confirmation_sent_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'starts_at']);
            $table->index(['business_id', 'staff_id', 'starts_at']);
            $table->index(['business_id', 'status']);
        });

        Schema::create('appointment_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_status_logs');
        Schema::dropIfExists('appointments');
    }
};
