<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->comment('panele giris yapabilen personel');
            $table->string('name');
            $table->string('title')->nullable()->comment('unvan: usta, stilist vb.');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('color', 7)->default('#7f56da')->comment('takvim rengi');
            $table->string('photo_path')->nullable();
            $table->boolean('accepts_online_booking')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
