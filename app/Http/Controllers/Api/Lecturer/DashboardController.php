<?php

namespace App\Http\Controllers\Api\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\CourseTopic;
use App\Models\ParticipationCriterion;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;

/** JSON mirror of DashboardController::lecturer() for the desktop client. */
class DashboardController extends Controller
{
    public function index(Request $request)
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

        $nextClass = $upcomingOwnQuizzes->first();

        $stats = [
            'quizzes' => Quiz::count(),
            'active_quizzes' => Quiz::where('status', '!=', 'draft')->get()
                ->filter(fn (Quiz $quiz) => in_array($quiz->stage(), ['scheduled', 'due_soon', 'active'], true))
                ->count(),
            'published_this_week' => Quiz::where('status', '!=', 'draft')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'students' => User::where('role', 'student')->count(),
            'total_topics' => $user->assignedTopics()->count(),
            'upcoming_classes' => $upcomingOwnQuizzes->count(),
            'next_class_scheduled_at' => $nextClass?->scheduled_at?->toIso8601String(),
            'average_score_percent' => $averageScorePercent,
        ];

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

        $quizzesByStatus = $this->quizzesByStatus($user);
        $criteria = ParticipationCriterion::forLecturer($user);
        $discussionStats = $this->lecturerDiscussionStats($user);

        return response()->json([
            'stats' => $stats,
            'recent_questions' => $recentQuestions,
            'unanswered_questions_count' => $unansweredQuestionsCount,
            'quizzes_by_status' => $quizzesByStatus,
            'participation_criteria' => [
                'points_per_question' => $criteria->points_per_question,
                'points_per_answer' => $criteria->points_per_answer,
                'points_per_like_received' => $criteria->points_per_like_received,
                'target_points' => $criteria->target_points,
            ],
            'discussion_stats' => [
                'new_threads_this_week' => $discussionStats['new_threads_this_week'],
                'unresolved_count' => $discussionStats['unresolved_count'],
                'participants_count' => $discussionStats['participants_count'],
                'top_topic_title' => $discussionStats['top_topic']?->title,
            ],
        ]);
    }

    public function updateParticipationCriteria(Request $request)
    {
        $validated = $request->validate([
            'points_per_question' => ['required', 'integer', 'min:0', 'max:100'],
            'points_per_answer' => ['required', 'integer', 'min:0', 'max:100'],
            'points_per_like_received' => ['required', 'integer', 'min:0', 'max:100'],
            'target_points' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        ParticipationCriterion::forLecturer($request->user())->update($validated);

        return response()->json(['message' => 'Participation criteria saved.']);
    }

    protected function quizzesByStatus(User $user)
    {
        $counts = $user->quizzes()->get()->countBy(fn (Quiz $quiz) => $quiz->stage());

        $stages = ['draft', 'planned', 'scheduled', 'due_soon', 'active', 'closed'];
        $max = max($counts->max() ?: 1, 1);

        return collect($stages)->map(fn ($status) => [
            'status' => $status,
            'label' => ucfirst(str_replace('_', ' ', $status)),
            'total' => (int) ($counts[$status] ?? 0),
            'pct' => (int) round((($counts[$status] ?? 0) / $max) * 100),
        ]);
    }

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
}
