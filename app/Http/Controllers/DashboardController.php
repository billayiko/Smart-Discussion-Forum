<?php

namespace App\Http\Controllers;

use App\Models\Answer;
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

        $liveQuiz = Quiz::liveFor($user);

        if ($liveQuiz) {
            return redirect()->route('quizzes.take', $liveQuiz);
        }

        $upcomingQuizAnnouncements = Quiz::upcomingFor($user)->take(3)->values();

        $ownAttempts = QuizAttempt::where('user_id', $user->id)->get();
        $averageGradePercent = $ownAttempts->isNotEmpty()
            ? (int) round($ownAttempts->avg(fn (QuizAttempt $a) => $a->total > 0 ? ($a->score / $a->total) * 100 : 0))
            : null;

        $stats = [
            'enrolled_lectures' => $user->subscribedTopics()->count(),
            'new_subscriptions_this_week' => $user->subscribedTopics()->wherePivot('created_at', '>=', now()->subDays(7))->count(),
            'quizzes' => Quiz::where('status', '!=', 'draft')->count(),
            'upcoming_classes' => $upcomingQuizAnnouncements->count(),
            'next_class' => $upcomingQuizAnnouncements->first(),
            'average_grade' => $averageGradePercent !== null ? $averageGradePercent.'%' : '—',
            'graded_quiz_count' => $ownAttempts->count(),
        ];

        $upcomingQuizzes = Quiz::where('status', '!=', 'draft')
            ->latest('scheduled_at')
            ->get()
            ->filter(fn (Quiz $quiz) => $quiz->isTargetedAt($user))
            ->take(4)
            ->values();

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

        $ownQuizzes = $user->quizzes()->get();
        $upcomingOwnQuizzes = $ownQuizzes
            ->filter(fn (Quiz $quiz) => $quiz->status !== 'draft' && ! $quiz->hasStarted())
            ->sortBy('scheduled_at')
            ->values();

        $ownAttempts = QuizAttempt::whereIn('quiz_id', $ownQuizzes->pluck('id'))->get();
        $averageScorePercent = $ownAttempts->isNotEmpty()
            ? (int) round($ownAttempts->avg(fn (QuizAttempt $a) => $a->total > 0 ? ($a->score / $a->total) * 100 : 0))
            : null;

        $stats = [
            'quizzes' => Quiz::count(),
            'active_quizzes' => Quiz::where('status', '!=', 'draft')->get()
                ->filter(fn (Quiz $quiz) => in_array($quiz->stage(), ['scheduled', 'due_soon', 'active'], true))
                ->count(),
            'published_this_week' => Quiz::where('status', '!=', 'draft')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'students' => User::where('role', 'student')->count(),
            'total_topics' => $user->assignedTopics()->count(),
            'upcoming_classes' => $upcomingOwnQuizzes->count(),
            'next_class' => $upcomingOwnQuizzes->first(),
            'average_score_percent' => $averageScorePercent,
        ];

        [$recentQuestions, $unansweredQuestionsCount] = $this->questionsPanelData();

        $quizzesByStatus = $this->quizzesByStatus($user);

        $participationCriteria = ParticipationCriterion::forLecturer($user);

        $discussionStats = $this->lecturerDiscussionStats($user);

        return view('pages.dashboards.lecturer', compact('user', 'stats', 'recentQuestions', 'unansweredQuestionsCount', 'quizzesByStatus', 'participationCriteria', 'discussionStats'));
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
            ->each(function (Quiz $quiz) {
                $attempts = QuizAttempt::where('quiz_id', $quiz->id)->get();
                $quiz->average_score_percent = $attempts->isNotEmpty()
                    ? (int) round($attempts->avg(fn (QuizAttempt $a) => $a->total > 0 ? ($a->score / $a->total) * 100 : 0))
                    : null;
            });

        return view('pages.dashboards.admin', compact('user', 'bubbles', 'quizzes', 'statusFilter', 'search'));
    }

    /**
     * A lecturer's discussion activity scoped to their own assigned topics:
     * new threads this week, unresolved questions, distinct participants,
     * and the topic with the most questions.
     */
    protected function lecturerDiscussionStats(User $user): array
    {
        $topicIds = $user->assignedTopics()->pluck('id');
        $questions = Question::whereIn('course_topic_id', $topicIds)->withCount('answers')->get();

        $newThreadsThisWeek = $questions->where('created_at', '>=', now()->startOfWeek())->count();
        $unresolvedCount = $questions->where('answers_count', 0)->count();

        $participantIds = $questions->pluck('user_id');
        $answererIds = Answer::whereIn('question_id', $questions->pluck('id'))->pluck('user_id');
        $participantsCount = $participantIds->merge($answererIds)->unique()->count();

        $topTopic = $topicIds->isEmpty() ? null : CourseTopic::whereIn('id', $topicIds)
            ->withCount(['questions'])
            ->orderByDesc('questions_count')
            ->first();

        return [
            'new_threads_this_week' => $newThreadsThisWeek,
            'unresolved_count' => $unresolvedCount,
            'participants_count' => $participantsCount,
            'top_topic' => ($topTopic && $topTopic->questions_count > 0) ? $topTopic : null,
        ];
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
        // Grouped from the live stage(), not the stored status column: nothing
        // ever updates that column after creation, so a raw SQL groupBy would
        // just reflect whatever the lecturer picked once and never touched again.
        $counts = $user->quizzes()->get()->countBy(fn (Quiz $quiz) => $quiz->stage());

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
