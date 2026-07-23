<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Complaint;
use App\Models\CourseTopic;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

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

        return view('pages.dashboards.admin.analytics.index', compact(
            'user',
            'summary',
            'topAskers',
            'topAnswerers',
            'topTopics',
            'topLecturers'
        ));
    }

    /**
     * Statistics scoped to a single topic (group) — its own subscriber count,
     * question/answer volume, complaints, quiz performance, and top
     * contributors, independent of every other topic on the platform.
     */
    public function show(Request $request, CourseTopic $topic)
    {
        $user = $request->user();

        $topic->loadCount('subscribers');
        $topic->load('lecturer');

        $questionIds = $topic->questions()->pluck('id');

        $summary = [
            'subscribers' => $topic->subscribers_count,
            'questions' => $questionIds->count(),
            'unanswered_questions' => $topic->questions()->doesntHave('answers')->count(),
            'answers' => Answer::whereIn('question_id', $questionIds)->count(),
            'pending_complaints' => Complaint::whereIn('question_id', $questionIds)->where('status', 'pending')->count(),
            'quizzes' => $topic->quizzes()->count(),
        ];

        $attempts = QuizAttempt::whereIn('quiz_id', $topic->quizzes()->pluck('id'))->get();
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

        return view('pages.dashboards.admin.analytics.show', compact('user', 'topic', 'summary', 'topAskers', 'topAnswerers'));
    }
}
