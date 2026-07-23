<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\CourseTopic;
use App\Models\ParticipationCriterion;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TopicController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $topics = CourseTopic::with('lecturer')->withCount('subscribers')->orderBy('title')->get();
        $subscribedTopicIds = $user->subscribedTopics()->pluck('course_topics.id');

        return view('pages.dashboards.student.topics', compact('user', 'topics', 'subscribedTopicIds'));
    }

    public function show(Request $request, CourseTopic $topic)
    {
        $user = $request->user();

        $groupMembers = $topic->subscribers()->get()
            ->when($topic->lecturer, fn ($members) => $members->push($topic->lecturer))
            ->unique('id')
            ->values();

        $siblingThreads = $topic->questions()
            ->with('user')
            ->withCount('answers')
            ->orderByDesc('updated_at')
            ->get();

        $otherTopics = CourseTopic::withCount('subscribers')
            ->where('id', '!=', $topic->id)
            ->orderBy('title')
            ->get();

        $quiz = Quiz::where('course_topic_id', $topic->id)
            ->whereNotIn('status', ['draft'])
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->first();

        $canAsk = in_array($user->role, ['student', 'lecturer'], true);

        $participationLeaderboard = $this->participationLeaderboard($topic, $groupMembers);
        $recentActivity = $this->recentActivity($topic, $groupMembers);

        return view('pages.dashboards.questions.topic-show', compact(
            'topic', 'user', 'groupMembers', 'siblingThreads', 'otherTopics', 'quiz', 'canAsk',
            'participationLeaderboard', 'recentActivity'
        ));
    }

    /**
     * A downloadable PDF of a topic's discussion threads and their answers,
     * available to any member so they can keep an offline copy.
     */
    public function exportPdf(Request $request, CourseTopic $topic)
    {
        $viewer = $request->user();

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

        return $pdf->download(str($topic->title)->slug().'-discussions.pdf');
    }

    /**
     * A CSV of the topic's participation leaderboard, for the topic's own
     * lecturer (or an admin) to import into a gradebook. Not exposed to
     * students — they can already see the live leaderboard on the page.
     */
    public function exportParticipationCsv(Request $request, CourseTopic $topic)
    {
        $viewer = $request->user();

        abort_unless($viewer->role === 'admin' || $viewer->id === $topic->lecturer_id, 403);

        $groupMembers = $topic->subscribers()->get()
            ->when($topic->lecturer, fn ($members) => $members->push($topic->lecturer))
            ->unique('id')
            ->values();

        $leaderboard = $this->participationLeaderboard($topic, $groupMembers);

        return response()->streamDownload(function () use ($leaderboard) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Posts', 'Score (%)']);

            foreach ($leaderboard as $row) {
                fputcsv($handle, [$row->user->name, $row->user->email, $row->posts, $row->score]);
            }

            fclose($handle);
        }, str($topic->title)->slug().'-participation.csv', ['Content-Type' => 'text/csv']);
    }

    public function subscribe(Request $request, CourseTopic $topic)
    {
        $request->user()->subscribedTopics()->syncWithoutDetaching([$topic->id]);

        return back()->with('success', "Subscribed to {$topic->title}.");
    }

    public function unsubscribe(Request $request, CourseTopic $topic)
    {
        $request->user()->subscribedTopics()->detach($topic->id);

        return back()->with('success', "Unsubscribed from {$topic->title}.");
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

        $questionIds = $topic->questions()->pluck('id');

        $questionCounts = Question::where('course_topic_id', $topic->id)
            ->selectRaw('user_id, count(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

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
                'icon' => 'fa-reply',
                'text' => "{$answer->user->name} replied to \"{$answer->question->title}\"",
                'at' => $answer->created_at,
            ]);

        $warnings = User::whereIn('id', $members->pluck('id'))
            ->whereNotNull('last_warned_at')
            ->latest('last_warned_at')
            ->take(5)
            ->get()
            ->map(fn (User $member) => (object) [
                'icon' => 'fa-triangle-exclamation',
                'text' => "Warning issued to {$member->name}",
                'at' => $member->last_warned_at,
            ]);

        $attempts = QuizAttempt::whereIn('quiz_id', $topic->quizzes()->pluck('id'))
            ->with(['user', 'quiz'])
            ->latest('submitted_at')
            ->take(5)
            ->get()
            ->map(fn (QuizAttempt $attempt) => (object) [
                'icon' => 'fa-circle-check',
                'text' => "\"{$attempt->quiz->title}\" auto-graded for {$attempt->user->name} ({$attempt->score}/{$attempt->total})",
                'at' => $attempt->submitted_at,
            ]);

        return $answers->concat($warnings)->concat($attempts)
            ->sortByDesc('at')
            ->take(6)
            ->values();
    }
}
