<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table): void {
            $table->boolean('is_required')->default(true)->after('is_published');
        });

        Schema::table('quizzes', function (Blueprint $table): void {
            $table->boolean('is_required')->default(true)->after('is_published');
        });

        Schema::table('assignments', function (Blueprint $table): void {
            $table->boolean('is_required')->default(true)->after('is_published');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', fn (Blueprint $table) => $table->dropColumn('is_required'));
        Schema::table('quizzes', fn (Blueprint $table) => $table->dropColumn('is_required'));
        Schema::table('assignments', fn (Blueprint $table) => $table->dropColumn('is_required'));
    }
};
