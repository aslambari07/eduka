<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedInteger('time_limit_minutes')->nullable();
            $table->unsignedInteger('attempts_allowed')->default(1);
            $table->decimal('pass_score', 5, 2)->default(70);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->unique(['course_id', 'slug']);
        });
    }

    public function down(): void { Schema::dropIfExists('quizzes'); }
};
