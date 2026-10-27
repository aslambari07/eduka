<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->longText('description');
            $table->timestamp('due_at')->nullable();
            $table->unsignedInteger('max_file_kb')->default(5120);
            $table->json('allowed_file_types')->nullable();
            $table->decimal('points', 8, 2)->default(100);
            $table->decimal('pass_score', 5, 2)->default(70);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('assignments'); }
};
