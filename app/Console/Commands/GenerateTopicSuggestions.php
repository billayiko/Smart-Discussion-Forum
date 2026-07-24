<?php

namespace App\Console\Commands;

use App\Models\Answer;
use App\Models\CourseTopic;
use App\Models\Question;
use App\Models\TopicSuggestionDismissal;
use App\Models\User;
use App\Notifications\TopicSuggested;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateTopicSuggestions extends Command
{
    protected $signature = 'topics:generate-suggestions';

    protected $description = "Suggest topics to students via collaborative filtering: for each student, find topics that other students with similar recent engagement are active in, and notify them to subscribe or ignore.";

    /**
     * Engagement weights per action, reflecting how much each signals
     * genuine interest in a topic (authoring > responding > reacting).
     */
    private const WEIGHT_QUESTION = 3;

    private const WEIGHT_ANSWER = 2;

    private const WEIGHT_LIKE = 1;

    public function handle(): int
    {
        $since = now()->subDays(30);
        $engagement = $this->buildEngagementMatrix($since);

        User::where('role', 'student')->each(fn (User $student) => $this->suggestForStudent($student, $engagement));

        return self::SUCCESS;
    }

    /**
     * @return array<int, array<int, int>> topic_id => [user_id => weighted engagement score]
     */
    protected function buildEngagementMatrix(CarbonInterface $since): array
    {
        $engagement = [];

        $accumulate = function (iterable $rows, int $weight) use (&$engagement): void {
            foreach ($rows as $row) {
                if (! $row->topic_id) {
                    continue;
                }

                $engagement[$row->topic_id][$row->user_id] = ($engagement[$row->topic_id][$row->user_id] ?? 0) + $weight;
            }
        };

        $accumulate(
            DB::table('questions')
                ->join('users', 'users.id', '=', 'questions.user_id')
                ->where('users.role', 'student')
                ->where('questions.created_at', '>=', $since)
                ->select('questions.course_topic_id as topic_id', 'questions.user_id as user_id')
                ->get(),
            self::WEIGHT_QUESTION
        );

        $accumulate(
            DB::table('answers')
                ->join('questions', 'questions.id', '=', 'answers.question_id')
                ->join('users', 'users.id', '=', 'answers.user_id')
                ->where('users.role', 'student')
                ->where('answers.created_at', '>=', $since)
                ->select('questions.course_topic_id as topic_id', 'answers.user_id as user_id')
                ->get(),
            self::WEIGHT_ANSWER
        );

        $accumulate(
            DB::table('likes')
                ->join('questions', 'questions.id', '=', 'likes.likeable_id')
                ->join('users', 'users.id', '=', 'likes.user_id')
                ->where('likes.likeable_type', Question::class)
                ->where('users.role', 'student')
                ->where('likes.created_at', '>=', $since)
                ->select('questions.course_topic_id as topic_id', 'likes.user_id as user_id')
                ->get(),
            self::WEIGHT_LIKE
        );

        $accumulate(
            DB::table('likes')
                ->join('answers', 'answers.id', '=', 'likes.likeable_id')
                ->join('questions', 'questions.id', '=', 'answers.question_id')
                ->join('users', 'users.id', '=', 'likes.user_id')
                ->where('likes.likeable_type', Answer::class)
                ->where('users.role', 'student')
                ->where('likes.created_at', '>=', $since)
                ->select('questions.course_topic_id as topic_id', 'likes.user_id as user_id')
                ->get(),
            self::WEIGHT_LIKE
        );

        return $engagement;
    }

    /**
     * Item-based collaborative filtering: seed on this student's most
     * engaged topics, find other students who share that engagement, then
     * rank topics those neighbors engage with (that this student isn't
     * subscribed to) by shared engagement strength.
     *
     * @param  array<int, array<int, int>>  $engagement
     */
    protected function suggestForStudent(User $student, array $engagement): void
    {
        $subscribedIds = $student->subscribedTopics()->pluck('course_topics.id')->all();

        $seedTopicIds = collect($engagement)
            ->map(fn (array $users) => $users[$student->id] ?? 0)
            ->filter(fn (int $score) => $score > 0)
            ->sortDesc()
            ->take(3)
            ->keys();

        if ($seedTopicIds->isEmpty()) {
            return;
        }

        $neighborScores = [];
        foreach ($seedTopicIds as $topicId) {
            foreach ($engagement[$topicId] ?? [] as $userId => $score) {
                if ($userId !== $student->id) {
                    $neighborScores[$userId] = ($neighborScores[$userId] ?? 0) + $score;
                }
            }
        }

        if (empty($neighborScores)) {
            return;
        }

        $candidateScores = [];
        foreach ($engagement as $topicId => $users) {
            if (in_array($topicId, $subscribedIds, true) || $seedTopicIds->contains($topicId)) {
                continue;
            }

            foreach ($users as $userId => $score) {
                if (isset($neighborScores[$userId])) {
                    $candidateScores[$topicId] = ($candidateScores[$topicId] ?? 0) + min($score, $neighborScores[$userId]);
                }
            }
        }

        if (empty($candidateScores)) {
            return;
        }

        arsort($candidateScores);

        $dismissals = TopicSuggestionDismissal::where('user_id', $student->id)->get()->keyBy('course_topic_id');

        $alreadySuggestedTopicIds = $student->unreadNotifications()
            ->where('type', TopicSuggested::class)
            ->get()
            ->map(fn ($notification) => $notification->data['topic_id'] ?? null)
            ->filter()
            ->all();

        foreach ($candidateScores as $topicId => $score) {
            $dismissal = $dismissals->get($topicId);

            if ($dismissal && $dismissal->isPermanent()) {
                continue;
            }

            if (in_array($topicId, $alreadySuggestedTopicIds, true)) {
                continue;
            }

            $topic = CourseTopic::find($topicId);

            if ($topic) {
                $student->notify(new TopicSuggested($topic));
            }

            return;
        }
    }
}
