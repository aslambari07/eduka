<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;
    protected $fillable = ['course_id', 'title', 'slug', 'summary', 'content', 'type', 'position', 'estimated_minutes', 'is_published', 'is_required'];
    protected function casts(): array { return ['is_published' => 'boolean', 'is_required' => 'boolean']; }
    public function course() { return $this->belongsTo(Course::class); }
    public function progress() { return $this->hasMany(LessonProgress::class); }
    public function quizzes() { return $this->hasMany(Quiz::class); }
    public function assignments() { return $this->hasMany(Assignment::class); }
}
