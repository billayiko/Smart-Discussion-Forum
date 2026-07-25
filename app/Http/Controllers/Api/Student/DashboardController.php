<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

/** JSON mirror of DashboardController::student() for the desktop client. */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $upcomingQuizAnnouncements = Quiz::upcomingFor($user)->take(3)->values();

        $ownAttempts = QuizAttempt::where('user_id', $user->id)->get();
        $averageGradePercent = $ownAttempts->isNotEmpty()
            ? (int) round($ownAttempts->avg(fn (QuizAttempt $a) => $a->total > 0 ? ($a->score / $a->total) * 100 : 0))
            : null;

        $nextClass = $upcomingQuizAnnouncements->first();

        $stats = [
            'enrolled_lectures' => $user->subscribedTopics()->count(),
            'new_subscriptions_this_week' => $user->subscribedTopics()->wherePivot('created_at', '>=', now()->subDays(7))->count(),
            'quizzes' => Quiz::where('status', '!=', 'draft')->count(),
            'upcoming_classes' => $upcomingQuizAnnouncements->count(),
            'next_class_scheduled_at' => $nextClass?->scheduled_at?->toIso8601String(),
            'average_grade_percent' => $averageGradePercent,
            'graded_quiz_count' => $ownAttempts->count(),
        ];

        $upcomingQuizzes = Quiz::where('status', '!=', 'draft')
            ->latest('scheduled_at')
            ->get()
            ->filter(fn (Quiz $quiz) => $quiz->isTargetedAt($user))
            ->take(4)
            ->values()
            ->map(fn (Quiz $quiz) => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'subject' => $quiz->subject,
                'duration_minutes' => $quiz->duration_minutes,
                'has_started' => $quiz->hasStarted(),
                'scheduled_at' => $quiz->scheduled_at?->toIso8601String(),
            ]);

        $recentQuestions = Question::with(['user', 'topic'])
            ->withCount('answers')
            ->orderByRaw('answers_count = 0 desc')
            ->latest()
            ->take(4)
            ->get()
            ->map(fn (Question $question) => [
                'id' => $question->id,
                'title' => $question->title,
                'topic_title' => $question->topic->title ?? 'Other',
                'user_name' => $question->user->name,
                'answers_count' => $question->answers_count,
            ]);

        $unansweredQuestionsCount = Question::doesntHave('answers')->count();

        $quizzesBySubject = $this->quizzesBySubject();

        $totalQuestionsCount = Question::count();
        $answeredRate = $totalQuestionsCount > 0
            ? (int) round((($totalQuestionsCount - $unansweredQuestionsCount) / $totalQuestionsCount) * 100)
            : 0;

        return response()->json([
            'stats' => $stats,
            'upcoming_quizzes' => $upcomingQuizzes,
            'upcoming_quiz_announcements' => $upcomingQuizAnnouncements->map(fn (Quiz $quiz) => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'subject' => $quiz->subject,
                'duration_minutes' => $quiz->duration_minutes,
                'scheduled_at' => $quiz->scheduled_at?->toIso8601String(),
            ]),
            'recent_questions' => $recentQuestions,
            'unanswered_questions_count' => $unansweredQuestionsCount,
            'quizzes_by_subject' => $quizzesBySubject,
            'answered_rate' => $answeredRate,
        ]);
    }

    /**
     * Non-draft quiz counts grouped by subject, with each row's bar width
     * pre-computed as a percentage of the largest subject's count.
     */
    protected function quizzesBySubject()
    {
        $rows = Quiz::where('status', '!=', 'draft')
            ->selectRaw('subject, count(*) as total')
            ->groupBy('subject')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $max = (int) $rows->max('total') ?: 1;

        return $rows->map(fn ($row) => [
            'subject' => $row->subject,
            'total' => (int) $row->total,
            'pct' => (int) round(($row->total / $max) * 100),
        ]);
    }
}
