<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'status', 'bio'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_INSTRUCTOR = 'pengajar';
    public const ROLE_PARTICIPANT = 'peserta';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function isAdmin(): bool { return $this->role === self::ROLE_ADMIN; }
    public function isInstructor(): bool { return $this->role === self::ROLE_INSTRUCTOR; }
    public function isParticipant(): bool { return $this->role === self::ROLE_PARTICIPANT; }

    public function coursesTaught() { return $this->hasMany(Course::class, 'instructor_id'); }
    public function enrollments() { return $this->hasMany(Enrollment::class); }
    public function lessonProgress() { return $this->hasMany(LessonProgress::class); }
    public function quizAttempts() { return $this->hasMany(QuizAttempt::class); }
    public function assignmentSubmissions() { return $this->hasMany(AssignmentSubmission::class); }
    public function notes() { return $this->hasMany(Note::class); }
    public function certificates() { return $this->hasMany(Certificate::class); }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
