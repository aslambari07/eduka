<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;
    protected $fillable = ['quiz_id', 'user_id', 'attempt_number', 'score', 'passed', 'started_at', 'submitted_at'];
    protected function casts(): array { return ['score' => 'decimal:2', 'passed' => 'boolean', 'started_at' => 'datetime', 'submitted_at' => 'datetime']; }
    public function quiz() { return $this->belongsTo(Quiz::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function answers() { return $this->hasMany(QuizAnswer::class, 'attempt_id'); }
}
