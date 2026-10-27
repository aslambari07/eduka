<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;
    protected $fillable = ['quiz_id', 'question', 'position', 'points', 'explanation'];
    protected function casts(): array { return ['points' => 'decimal:2']; }
    public function quiz() { return $this->belongsTo(Quiz::class); }
    public function options() { return $this->hasMany(QuizOption::class, 'question_id')->orderBy('position'); }
    public function answers() { return $this->hasMany(QuizAnswer::class, 'question_id'); }
}
