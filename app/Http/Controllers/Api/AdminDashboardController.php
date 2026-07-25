<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\CourseTopic;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;

/** JSON mirror of DashboardController::admin() for the desktop client. Route is role:admin-gated. */
class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $bubbles = [
            'topics' => CourseTopic::count(),
            'unassigned_topics' => CourseTopic::whereNull('lecturer_id')->count(),
            'questions' => Question::count(),
            'unanswered_questions' => Question::doesntHave('answers')->count(),
            'pending_complaints' => Complaint::where('status', 'pending')->count(),
            'quizzes' => Quiz::count(),
            'published_quizzes' => Quiz::where('status', '!=', 'draft')->count(),
            'students' => User::where('role', 'student')->count(),
            'lecturers' => User::where('role', 'lecturer')->count(),
        ];

        $statusFilter = $request->query('status');
        $search = $request->query('q');

        // Marks stay invisible to the admin until the owning lecturer
        // confirms them, so unconfirmed quizzes are left out entirely here.
        $quizzes = Quiz::whereNotNull('marks_confirmed_at')
            ->when($statusFilter === 'published', fn ($query) => $query->where('status', '!=', 'draft'))
            ->when($statusFilter === 'draft', fn ($query) => $query->where('status', 'draft'))
            ->when($statusFilter === 'scheduled', fn ($query) => $query->where('status', 'scheduled'))
            ->when($search, fn ($query) => $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('subject', 'like', "%{$search}%")))
            ->withCount('attempts')
            ->latest()
            ->take(5)
            ->get()
            ->map(function (Quiz $quiz) {
                $attempts = QuizAttempt::where('quiz_id', $quiz->id)->get();
                $averageScorePercent = $attempts->isNotEmpty()
                    ? (int) round($attempts->avg(fn (QuizAttempt $a) => $a->total > 0 ? ($a->score / $a->total) * 100 : 0))
                    : null;

                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'subject' => $quiz->subject,
                    'total_questions' => $quiz->total_questions,
                    'duration_minutes' => $quiz->duration_minutes,
                    'attempts_count' => $quiz->attempts_count,
                    'average_score_percent' => $averageScorePercent,
                    'stage' => $quiz->stage(),
                ];
            });

        return response()->json([
            'bubbles' => $bubbles,
            'quizzes' => $quizzes,
        ]);
    }
}
