<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;
    protected $fillable = ['course_id', 'lesson_id', 'title', 'slug', 'description', 'time_limit_minutes', 'attempts_allowed', 'pass_score', 'is_published', 'is_required'];
    protected function casts(): array { return ['is_published' => 'boolean', 'is_required' => 'boolean', 'pass_score' => 'decimal:2']; }
    public function course() { return $this->belongsTo(Course::class); }
    public function lesson() { return $this->belongsTo(Lesson::class); }
    public function questions() { return $this->hasMany(QuizQuestion::class)->orderBy('position'); }
    public function attempts() { return $this->hasMany(QuizAttempt::class); }
}
