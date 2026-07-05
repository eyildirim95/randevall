<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->nullable();
            $table->text('content');
            $table->string('color', 20)->default('warning')->comment('bootstrap rengi: warning, info, success...');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_task')->default(false)->comment('yapilacak is mi');
            $table->boolean('is_completed')->default(false);
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'is_pinned']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
