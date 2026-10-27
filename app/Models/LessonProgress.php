<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    use HasFactory;
    protected $table = 'lesson_progress';
    protected $fillable = ['lesson_id', 'user_id', 'completed_at', 'last_viewed_at'];
    protected function casts(): array { return ['completed_at' => 'datetime', 'last_viewed_at' => 'datetime']; }
    public function lesson() { return $this->belongsTo(Lesson::class); }
    public function user() { return $this->belongsTo(User::class); }
}
