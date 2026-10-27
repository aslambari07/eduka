<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    use HasFactory;
    protected $fillable = ['category_id', 'instructor_id', 'title', 'slug', 'short_description', 'description', 'level', 'visibility', 'status', 'duration_minutes', 'price', 'published_at'];
    protected function casts(): array { return ['published_at' => 'datetime', 'price' => 'decimal:2']; }
    public function category() { return $this->belongsTo(Category::class); }
    public function instructor() { return $this->belongsTo(User::class, 'instructor_id'); }
    public function lessons() { return $this->hasMany(Lesson::class)->orderBy('position'); }
    public function enrollments() { return $this->hasMany(Enrollment::class); }
    public function quizzes() { return $this->hasMany(Quiz::class); }
    public function assignments() { return $this->hasMany(Assignment::class); }
    public function notes() { return $this->hasMany(Note::class); }
    public function certificates() { return $this->hasMany(Certificate::class); }
    public function prerequisites(): BelongsToMany { return $this->belongsToMany(self::class, 'course_prerequisites', 'course_id', 'prerequisite_course_id'); }
    public function dependentCourses(): BelongsToMany { return $this->belongsToMany(self::class, 'course_prerequisites', 'prerequisite_course_id', 'course_id'); }
    public function scopePublished($query) { return $query->where('status', 'published')->where('visibility', 'public'); }
    public function getLevelLabelAttribute(): string { return ucfirst($this->level); }
}
