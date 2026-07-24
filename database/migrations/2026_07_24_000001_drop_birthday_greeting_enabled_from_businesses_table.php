<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('businesses', 'birthday_greeting_enabled')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->dropColumn('birthday_greeting_enabled');
            });
        }
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('birthday_greeting_enabled')->default(false)->after('loyalty_reward_description');
        });
    }
};
