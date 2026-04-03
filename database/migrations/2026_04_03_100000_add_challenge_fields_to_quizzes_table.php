<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('is_challenge')->default(false)->after('sort_order');
            $table->unsignedInteger('challenge_window_size')->nullable()->after('is_challenge');
            $table->unsignedInteger('challenge_min_stars')->nullable()->after('challenge_window_size');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn([
                'is_challenge',
                'challenge_window_size',
                'challenge_min_stars',
            ]);
        });
    }
};

