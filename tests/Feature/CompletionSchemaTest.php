<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompletionSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_learning_content_defaults_to_required_and_can_be_optional(): void
    {
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR]);
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'Schema completion',
            'slug' => 'schema-completion',
            'status' => 'draft',
            'visibility' => 'private',
            'level' => 'pemula',
        ]);

        $lesson = Lesson::create(['course_id' => $course->id, 'title' => 'Wajib', 'slug' => 'wajib', 'position' => 1]);
        $quiz = Quiz::create(['course_id' => $course->id, 'title' => 'Kuis', 'slug' => 'kuis', 'attempts_allowed' => 1, 'pass_score' => 70]);
        $assignment = Assignment::create(['course_id' => $course->id, 'title' => 'Tugas', 'description' => 'Tugas', 'is_published' => true]);

        $this->assertTrue($lesson->fresh()->is_required);
        $this->assertTrue($quiz->fresh()->is_required);
        $this->assertTrue($assignment->fresh()->is_required);

        $lesson->update(['is_required' => false]);
        $quiz->update(['is_required' => false]);
        $assignment->update(['is_required' => false]);

        $this->assertFalse($lesson->fresh()->is_required);
        $this->assertFalse($quiz->fresh()->is_required);
        $this->assertFalse($assignment->fresh()->is_required);
    }
}
