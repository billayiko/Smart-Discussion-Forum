<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\CourseTopic;
use App\Models\ParticipationCriterion;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function student(Request $request)
    {
        $user = $request->user();

        $attemptedQuizIds = QuizAttempt::where('user_id', $user->id)->pluck('quiz_id');

        $liveQuiz = Quiz::where('status', '!=', 'draft')
            ->whereNotNull('scheduled_at')
            ->whereNotNull('questions_finalized_at')
            ->where('scheduled_at', '<=', now())
            ->whereNotIn('id', $attemptedQuizIds)
            ->orderBy('scheduled_at')
            ->get()
            ->first(fn (Quiz $quiz) => $quiz->isLive());

        if ($liveQuiz) {
            return redirect()->route('quizzes.take', $liveQuiz);
        }

        $stats = [
            'enrolled_lectures' => 6,
            'quizzes' => Quiz::where('status', '!=', 'draft')->count(),
            'upcoming_classes' => 4,
            'average_grade' => 'A-',
        ];

        $upcomingQuizzes = Quiz::where('status', '!=', 'draft')
            ->latest('scheduled_at')
            ->take(4)
            ->get();

        $upcomingQuizAnnouncements = Quiz::where('status', '!=', 'draft')
            ->whereNotNull('scheduled_at')
            ->whereNotNull('questions_finalized_at')
            ->where('scheduled_at', '>', now())
            ->whereNotIn('id', $attemptedQuizIds)
            ->orderBy('scheduled_at')
            ->take(3)
            ->get();

        [$recentQuestions, $unansweredQuestionsCount] = $this->questionsPanelData();

        $quizzesBySubject = $this->quizzesBySubject();

        $totalQuestionsCount = Question::count();
        $answeredRate = $totalQuestionsCount > 0
            ? (int) round((($totalQuestionsCount - $unansweredQuestionsCount) / $totalQuestionsCount) * 100)
            : 0;

        return view('pages.dashboards.student', compact('user', 'stats', 'upcomingQuizzes', 'upcomingQuizAnnouncements', 'recentQuestions', 'unansweredQuestionsCount', 'quizzesBySubject', 'answeredRate'));
    }

    public function lecturer(Request $request)
    {
        $user = $request->user();

        $stats = [
            'quizzes' => Quiz::count(),
            'active_quizzes' => Quiz::whereIn('status', ['scheduled', 'due_soon', 'active'])->count(),
            'published_this_week' => Quiz::where('status', '!=', 'draft')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'students' => User::where('role', 'student')->count(),
        ];

        [$recentQuestions, $unansweredQuestionsCount] = $this->questionsPanelData();

        $quizzesByStatus = $this->quizzesByStatus($user);

        $participationCriteria = ParticipationCriterion::forLecturer($user);

        return view('pages.dashboards.lecturer', compact('user', 'stats', 'recentQuestions', 'unansweredQuestionsCount', 'quizzesByStatus', 'participationCriteria'));
    }

    public function admin(Request $request)
    {
        $user = $request->user();

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

        $quizzes = Quiz::latest()->take(5)->get();

        return view('pages.dashboards.admin', compact('user', 'bubbles', 'quizzes'));
    }

    protected function questionsPanelData(): array
    {
        $recentQuestions = Question::with(['user', 'topic'])
            ->withCount('answers')
            ->orderByRaw('answers_count = 0 desc')
            ->latest()
            ->take(4)
            ->get();

        $unansweredQuestionsCount = Question::doesntHave('answers')->count();

        return [$recentQuestions, $unansweredQuestionsCount];
    }

    /**
     * Non-draft quiz counts grouped by subject, with each row's bar width
     * pre-computed as a percentage of the largest subject's count.
     */
    protected function quizzesBySubject(): Collection
    {
        $rows = Quiz::where('status', '!=', 'draft')
            ->selectRaw('subject, count(*) as total')
            ->groupBy('subject')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $max = (int) $rows->max('total') ?: 1;

        return $rows->map(function ($row) use ($max) {
            $row->pct = (int) round(($row->total / $max) * 100);

            return $row;
        });
    }

    /**
     * A lecturer's own quizzes grouped by workflow status, in a fixed stage
     * order, with each row's bar width pre-computed as a percentage of the
     * largest status's count.
     */
    protected function quizzesByStatus(User $user): Collection
    {
        $counts = $user->quizzes()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stages = ['draft', 'planned', 'scheduled', 'due_soon', 'active', 'closed'];
        $max = max($counts->max() ?: 1, 1);

        return collect($stages)->map(fn ($status) => (object) [
            'status' => $status,
            'label' => ucfirst(str_replace('_', ' ', $status)),
            'total' => (int) ($counts[$status] ?? 0),
            'pct' => (int) round((($counts[$status] ?? 0) / $max) * 100),
        ]);
    }
}
