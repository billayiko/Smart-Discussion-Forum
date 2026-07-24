<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Complaint;
use App\Models\CourseTopic;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Mirrors Admin\AnalyticsController for the desktop client — same summary
 * and per-topic figures, admin-only (enforced by the `role:admin` route
 * middleware), returned as JSON instead of a Blade view.
 */
class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $summary = [
            'students' => User::where('role', 'student')->count(),
            'lecturers' => User::where('role', 'lecturer')->count(),
            'topics' => CourseTopic::count(),
            'questions' => Question::count(),
            'unanswered_questions' => Question::doesntHave('answers')->count(),
            'answers' => Answer::count(),
            'pending_complaints' => Complaint::where('status', 'pending')->count(),
        ];

        $topAskers = User::where('role', 'student')
            ->whereHas('questions')
            ->withCount('questions')
            ->orderByDesc('questions_count')
            ->take(5)
            ->get();

        $topAnswerers = User::whereIn('role', ['student', 'lecturer'])
            ->whereHas('answers')
            ->withCount('answers')
            ->orderByDesc('answers_count')
            ->take(5)
            ->get();

        $topTopics = CourseTopic::with('lecturer')
            ->whereHas('subscribers')
            ->withCount('subscribers')
            ->orderByDesc('subscribers_count')
            ->take(5)
            ->get();

        $topLecturers = User::where('role', 'lecturer')
            ->whereHas('assignedTopics')
            ->withCount('assignedTopics')
            ->orderByDesc('assigned_topics_count')
            ->take(5)
            ->get();

        return response()->json([
            'summary' => $summary,
            'top_askers' => $topAskers,
            'top_answerers' => $topAnswerers,
            'top_topics' => $topTopics,
            'top_lecturers' => $topLecturers,
        ]);
    }

    public function show(Request $request, CourseTopic $topic)
    {
        $topic->loadCount('subscribers');
        $topic->load('lecturer');

        $questionIds = $topic->questions()->pluck('id');
        $confirmedQuizIds = $topic->quizzes()->whereNotNull('marks_confirmed_at')->pluck('id');

        $summary = [
            'subscribers' => $topic->subscribers_count,
            'questions' => $questionIds->count(),
            'unanswered_questions' => $topic->questions()->doesntHave('answers')->count(),
            'answers' => Answer::whereIn('question_id', $questionIds)->count(),
            'pending_complaints' => Complaint::whereIn('question_id', $questionIds)->where('status', 'pending')->count(),
            'quizzes' => $confirmedQuizIds->count(),
        ];

        $attempts = QuizAttempt::whereIn('quiz_id', $confirmedQuizIds)->get();
        $summary['average_quiz_score'] = $attempts->isNotEmpty()
            ? (int) round($attempts->avg(fn (QuizAttempt $attempt) => $attempt->total > 0 ? ($attempt->score / $attempt->total) * 100 : 0))
            : null;

        $topAskers = User::where('role', 'student')
            ->whereHas('questions', fn ($query) => $query->whereIn('id', $questionIds))
            ->withCount(['questions' => fn ($query) => $query->whereIn('id', $questionIds)])
            ->orderByDesc('questions_count')
            ->take(5)
            ->get();

        $topAnswerers = User::whereIn('role', ['student', 'lecturer'])
            ->whereHas('answers', fn ($query) => $query->whereIn('question_id', $questionIds))
            ->withCount(['answers' => fn ($query) => $query->whereIn('question_id', $questionIds)])
            ->orderByDesc('answers_count')
            ->take(5)
            ->get();

        return response()->json([
            'topic' => $topic,
            'summary' => $summary,
            'top_askers' => $topAskers,
            'top_answerers' => $topAnswerers,
        ]);
    }
}
