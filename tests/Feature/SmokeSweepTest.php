<?php

namespace Tests\Feature;

use App\Models\CourseTopic;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeSweepTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_pages_do_not_500(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $topic = CourseTopic::create(['title' => 'Algorithms', 'lecturer_id' => $lecturer->id]);
        $question = Question::create(['user_id' => $student->id, 'course_topic_id' => $topic->id, 'title' => 'Q1', 'body' => 'Body text']);
        $quiz = Quiz::create(['user_id' => $lecturer->id, 'title' => 'Quiz 1', 'subject' => 'Math', 'duration_minutes' => 30]);

        $paths = [
            '/dashboard',
            '/topics',
            "/topics/{$topic->id}",
            '/questions',
            "/questions/{$question->id}",
            '/quizzes',
            '/messages',
        ];

        foreach ($paths as $path) {
            $response = $this->actingAs($student)->get($path);
            $this->assertLessThan(500, $response->status(), "Student GET {$path} returned {$response->status()}");
        }
    }

    public function test_lecturer_pages_do_not_500(): void
    {
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $topic = CourseTopic::create(['title' => 'Algorithms', 'lecturer_id' => $lecturer->id]);
        $quiz = Quiz::create(['user_id' => $lecturer->id, 'title' => 'Quiz 1', 'subject' => 'Math', 'duration_minutes' => 30]);

        $paths = [
            '/dashboard',
            '/topics',
            "/topics/{$topic->id}",
            '/quizzes',
            "/quizzes/{$quiz->id}/edit",
            '/messages',
        ];

        foreach ($paths as $path) {
            $response = $this->actingAs($lecturer)->get($path);
            $this->assertLessThan(500, $response->status(), "Lecturer GET {$path} returned {$response->status()}");
        }
    }

    public function test_admin_pages_do_not_500(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $paths = [
            '/dashboard',
            '/admin/members',
            '/admin/topics',
            '/admin/complaints',
            '/admin/analytics',
        ];

        foreach ($paths as $path) {
            $response = $this->actingAs($admin)->get($path);
            $this->assertLessThan(500, $response->status(), "Admin GET {$path} returned {$response->status()}");
        }
    }
}
