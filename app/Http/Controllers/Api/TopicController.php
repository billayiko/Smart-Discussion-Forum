<?php

namespace App\Http\Controllers\Api;

use App\Concerns\ChecksTopicAccess;
use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\CourseTopic;
use App\Models\ParticipationCriterion;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\TopicSuggestionDismissal;
use App\Models\User;
use App\Notifications\TopicSuggested;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TopicController extends Controller
{
    use ChecksTopicAccess;

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $topics = CourseTopic::withCount(['questions', 'subscribers'])->orderBy('title')->get();
        } else {
            $topicsQuery = $user->role === 'student' ? $user->subscribedTopics() : $user->assignedTopics();
            $topics = $topicsQuery->withCount(['questions', 'subscribers'])->orderBy('title')->get();
        }

        return response()->json($topics);
    }

    /**
     * Every topic in the system plus which ones this student is subscribed
     * to — mirrors the web's TopicController::index() (the "Topics" browse
     * page), distinct from this controller's own index() above (which
     * powers the Discussion Forum's "my topics" bubble list instead).
     */
    public function browse(Request $request)
    {
        $user = $request->user();

        $topics = CourseTopic::with('lecturer')->withCount('subscribers')->orderBy('title')->get();
        $subscribedTopicIds = $user->subscribedTopics()->pluck('course_topics.id');

        return response()->json([
            'topics' => $topics->map(fn (CourseTopic $topic) => [
                'id' => $topic->id,
                'title' => $topic->title,
                'description' => $topic->description,
                'lecturer_name' => $topic->lecturer->name ?? null,
                'subscribers_count' => $topic->subscribers_count,
            ]),
            'subscribed_topic_ids' => $subscribedTopicIds,
        ]);
    }

    public function subscribe(Request $request, CourseTopic $topic)
    {
        $request->user()->subscribedTopics()->syncWithoutDetaching([$topic->id]);

        $this->clearSuggestion($request->user(), $topic);

        return response()->json(['message' => "Subscribed to {$topic->title}."]);
    }

    public function unsubscribe(Request $request, CourseTopic $topic)
    {
        $request->user()->subscribedTopics()->detach($topic->id);

        return response()->json(['message' => "Unsubscribed from {$topic->title}."]);
    }

    public function ignoreSuggestion(Request $request, CourseTopic $topic)
    {
        $user = $request->user();

        $dismissal = TopicSuggestionDismissal::firstOrNew([
            'user_id' => $user->id,
            'course_topic_id' => $topic->id,
        ]);

        if (! $dismissal->exists) {
            $dismissal->first_dismissed_at = now();
        }

        $dismissal->last_dismissed_at = now();
        $dismissal->save();

        $this->clearSuggestion($user, $topic);

        return response()->json(['message' => "Won't suggest {$topic->title} again for now."]);
    }

    public function questions(Request $request, CourseTopic $topic)
    {
        $this->ensureTopicAccessible($request->user(), $topic);

        $questions = $topic->questions()
            ->with('user')
            ->withCount('answers')
            ->orderByDesc('updated_at')
            ->get();

        return response()->json($questions);
    }

    /**
     * The participation leaderboard and recent-activity feed shown on the
     * web's per-topic forum shell (_forum-topic.blade.php) — mirrors
     * TopicController::show()'s two panels, split into their own endpoint
     * since the desktop's topic-threads screen already gets its thread list
     * from questions() above.
     */
    public function leaderboardAndActivity(Request $request, CourseTopic $topic)
    {
        $this->ensureTopicAccessible($request->user(), $topic);

        $groupMembers = $topic->subscribers()->get()
            ->when($topic->lecturer, fn ($members) => $members->push($topic->lecturer))
            ->unique('id')
            ->values();

        return response()->json([
            'participation_leaderboard' => $this->participationLeaderboard($topic, $groupMembers)->map(fn ($row) => [
                'user_name' => $row->user->name,
                'posts' => $row->posts,
                'score' => $row->score,
            ]),
            'recent_activity' => $this->recentActivity($topic, $groupMembers)->map(fn ($event) => [
                'icon' => $event->icon,
                'text' => $event->text,
                'at' => $event->at->toIso8601String(),
            ]),
        ]);
    }

    public function exportPdf(Request $request, CourseTopic $topic)
    {
        $viewer = $request->user();
        $this->ensureTopicAccessible($viewer, $topic);

        $threads = $topic->questions()
            ->with(['user', 'answers.user', 'answers.excludedUsers'])
            ->orderByDesc('created_at')
            ->get()
            ->each(function (Question $thread) use ($viewer) {
                $thread->setRelation(
                    'answers',
                    $thread->answers->reject(fn (Answer $answer) => $answer->isExcludedFor($viewer))->values()
                );
            });

        $pdf = Pdf::loadView('pdf.topic-export', compact('topic', 'threads'));

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.str($topic->title)->slug().'-discussions.pdf"',
        ]);
    }

    public function exportParticipationCsv(Request $request, CourseTopic $topic)
    {
        $viewer = $request->user();
        abort_unless($viewer->role === 'admin' || $viewer->id === $topic->lecturer_id, 403);

        $groupMembers = $topic->subscribers()->get()
            ->when($topic->lecturer, fn ($members) => $members->push($topic->lecturer))
            ->unique('id')
            ->values();

        $leaderboard = $this->participationLeaderboard($topic, $groupMembers);

        $rows = "Name,Email,Posts,Score (%)\n";
        foreach ($leaderboard as $row) {
            $rows .= '"'.str_replace('"', '""', $row->user->name).'","'.$row->user->email.'",'.$row->posts.','.$row->score."\n";
        }

        return response($rows, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.str($topic->title)->slug().'-participation.csv"',
        ]);
    }

    /**
     * Each student subscriber's post count and participation score for this
     * topic, scored against the topic's lecturer's configured criteria.
     */
    protected function participationLeaderboard(CourseTopic $topic, Collection $members): Collection
    {
        $students = $members->filter(fn (User $member) => $member->role === 'student')->values();

        if ($students->isEmpty()) {
            return collect();
        }

        $criteria = $topic->lecturer
            ? ParticipationCriterion::forLecturer($topic->lecturer)
            : new ParticipationCriterion(['points_per_question' => 0, 'points_per_answer' => 0, 'points_per_like_received' => 0, 'target_points' => 1]);

        $questionCounts = Question::where('course_topic_id', $topic->id)
            ->selectRaw('user_id, count(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $questionIds = $topic->questions()->pluck('id');

        $answerCounts = Answer::whereIn('question_id', $questionIds)
            ->selectRaw('user_id, count(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $likesReceived = [];

        Question::where('course_topic_id', $topic->id)->withCount('likes')->get()->each(
            function (Question $question) use (&$likesReceived) {
                $likesReceived[$question->user_id] = ($likesReceived[$question->user_id] ?? 0) + $question->likes_count;
            }
        );

        Answer::whereIn('question_id', $questionIds)->withCount('likes')->get()->each(
            function (Answer $answer) use (&$likesReceived) {
                $likesReceived[$answer->user_id] = ($likesReceived[$answer->user_id] ?? 0) + $answer->likes_count;
            }
        );

        return $students
            ->map(function (User $student) use ($criteria, $questionCounts, $answerCounts, $likesReceived) {
                $questions = (int) ($questionCounts[$student->id] ?? 0);
                $answers = (int) ($answerCounts[$student->id] ?? 0);
                $likes = (int) ($likesReceived[$student->id] ?? 0);

                return (object) [
                    'user' => $student,
                    'posts' => $questions + $answers,
                    'score' => $criteria->scorePercentageFor($criteria->rawPointsFor($questions, $answers, $likes)),
                ];
            })
            ->sortByDesc('score')
            ->values();
    }

    /**
     * The latest replies, inactivity warnings, and auto-graded quiz results
     * relevant to this topic's members, merged into one feed.
     */
    protected function recentActivity(CourseTopic $topic, Collection $members): Collection
    {
        $questionIds = $topic->questions()->pluck('id');

        $answers = Answer::whereIn('question_id', $questionIds)
            ->with(['user', 'question'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (Answer $answer) => (object) [
                'icon' => 'reply',
                'text' => "{$answer->user->name} replied to \"{$answer->question->title}\"",
                'at' => $answer->created_at,
            ]);

        $warnings = User::whereIn('id', $members->pluck('id'))
            ->whereNotNull('last_warned_at')
            ->latest('last_warned_at')
            ->take(5)
            ->get()
            ->map(fn (User $member) => (object) [
                'icon' => 'warning',
                'text' => "Warning issued to {$member->name}",
                'at' => $member->last_warned_at,
            ]);

        $attempts = QuizAttempt::whereIn('quiz_id', $topic->quizzes()->pluck('id'))
            ->with(['user', 'quiz'])
            ->latest('submitted_at')
            ->take(5)
            ->get()
            ->map(fn (QuizAttempt $attempt) => (object) [
                'icon' => 'quiz',
                'text' => "\"{$attempt->quiz->title}\" auto-graded for {$attempt->user->name} ({$attempt->score}/{$attempt->total})",
                'at' => $attempt->submitted_at,
            ]);

        return $answers->concat($warnings)->concat($attempts)
            ->sortByDesc('at')
            ->take(6)
            ->values();
    }

    protected function clearSuggestion(User $user, CourseTopic $topic): void
    {
        $user->unreadNotifications()
            ->where('type', TopicSuggested::class)
            ->get()
            ->filter(fn ($notification) => ($notification->data['topic_id'] ?? null) === $topic->id)
            ->each->markAsRead();
    }
}
