<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class QuizManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturers_can_import_quizzes_from_csv(): void
    {
        $user = User::factory()->create(['role' => 'lecturer']);

        $file = UploadedFile::fake()->createWithContent(
            'quizzes.csv',
            "title,subject,total_questions,duration_minutes,scheduled_at,status\nIntro to Algorithms,Computer Science,10,30,2026-07-14 10:00:00,scheduled\n"
        );

        $response = $this
            ->actingAs($user)
            ->post(route('quizzes.import'), ['file' => $file]);

        $response->assertRedirect();
        $this->assertSame(1, Quiz::count());
        $this->assertSame('Intro to Algorithms', Quiz::first()->title);
    }

    public function test_students_cannot_access_quiz_management(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $response = $this
            ->actingAs($user)
            ->get(route('quizzes.index'));

        $response->assertForbidden();
    }
}
