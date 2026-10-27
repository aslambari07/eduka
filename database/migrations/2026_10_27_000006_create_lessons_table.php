<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('summary', 500)->nullable();
            $table->longText('content')->nullable();
            $table->string('type')->default('article');
            $table->unsignedInteger('position')->default(1);
            $table->unsignedInteger('estimated_minutes')->default(10);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->unique(['course_id', 'slug']);
            $table->unique(['course_id', 'position']);
        });
    }

    public function down(): void { Schema::dropIfExists('lessons'); }
};
