<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;
    protected $fillable = ['course_id', 'lesson_id', 'title', 'description', 'due_at', 'max_file_kb', 'allowed_file_types', 'points', 'pass_score', 'is_published', 'is_required'];
    protected function casts(): array { return ['due_at' => 'datetime', 'allowed_file_types' => 'array', 'is_published' => 'boolean', 'is_required' => 'boolean', 'points' => 'decimal:2', 'pass_score' => 'decimal:2']; }
    public function course() { return $this->belongsTo(Course::class); }
    public function lesson() { return $this->belongsTo(Lesson::class); }
    public function submissions() { return $this->hasMany(AssignmentSubmission::class); }
}
