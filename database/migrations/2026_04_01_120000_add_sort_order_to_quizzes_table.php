<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->nullable()->after('subject_id');
        });

        $quizzes = DB::table('quizzes')->orderBy('id')->get(['id']);
        foreach ($quizzes as $index => $quiz) {
            DB::table('quizzes')
                ->where('id', $quiz->id)
                ->update(['sort_order' => $index + 1]);
        }
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
