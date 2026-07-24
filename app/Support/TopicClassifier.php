<?php

namespace App\Support;

use App\Models\CourseTopic;
use Illuminate\Support\Collection;

/**
 * Content-based classifier: matches a question's text against each course
 * topic's title/description by shared keywords, so a question left as
 * "Other / General" can still be auto-filed under the topic it actually
 * belongs to instead of staying uncategorized, and a question posted into
 * the wrong topic's thread can be flagged for moderator review.
 *
 * Words are weighted by rarity across topics (like TF-IDF) rather than
 * counted flatly, since a topic's title/description here is often just a
 * couple of words — a single distinctive shared term (e.g. "javascript")
 * should outweigh several shared generic ones.
 */
class TopicClassifier
{
    /**
     * Minimum combined weight to trust a match. 1.0 is "found at least one
     * word unique to a single topic," the strongest possible single-word
     * signal.
     */
    private const MIN_SCORE = 1.0;

    /**
     * A topic needs at least this many of its own distinct keywords before
     * it's fair to judge content as not matching it — otherwise a topic
     * with a near-empty description would flag almost everything.
     */
    private const MIN_TOPIC_VOCABULARY = 3;

    private const STOPWORDS = [
        'the', 'and', 'for', 'are', 'but', 'not', 'you', 'all', 'can',
        'has', 'had', 'was', 'were', 'with', 'this', 'that', 'from',
        'have', 'what', 'when', 'where', 'why', 'how', 'does', 'doing',
        'about', 'into', 'your', 'their', 'them', 'they', 'his', 'her',
        'its', 'our', 'ours', 'who', 'whom', 'which', 'will', 'would',
        'should', 'could', 'than', 'then', 'there', 'here', 'some',
        'any', 'each', 'more', 'most', 'other', 'such', 'only', 'own',
        'same', 'get', 'got', 'need', 'want', 'like', 'just', 'also',
        'please', 'help', 'question', 'someone', 'anyone', 'thanks',
    ];

    /**
     * Best-matching topic for the given text, or null if nothing scores
     * above the minimum confidence threshold.
     */
    public function classify(string $text, ?Collection $topics = null): ?CourseTopic
    {
        $topics ??= CourseTopic::all();
        $scores = $this->scoreAll($text, $topics);

        if ($scores->isEmpty()) {
            return null;
        }

        $best = $scores->sortDesc();

        return $best->first() >= self::MIN_SCORE
            ? $topics->firstWhere('id', $best->keys()->first())
            : null;
    }

    /**
     * Whether the given text looks like it clearly belongs to a different
     * topic than the one it was posted under — i.e. some other topic
     * scores a confident match while this one scores nothing at all.
     * Returns that better topic, or null if the current one is a
     * reasonable fit (or there isn't enough signal to judge either way).
     */
    public function suggestBetterTopic(string $text, CourseTopic $currentTopic): ?CourseTopic
    {
        $topics = CourseTopic::all();
        $topicWords = collect($this->tokenize($currentTopic->title.' '.$currentTopic->description))->unique();

        if ($topicWords->count() < self::MIN_TOPIC_VOCABULARY) {
            return null;
        }

        $scores = $this->scoreAll($text, $topics);

        if (($scores[$currentTopic->id] ?? 0) > 0) {
            return null;
        }

        $best = $scores->sortDesc();

        if ($best->isEmpty() || $best->first() < self::MIN_SCORE) {
            return null;
        }

        $bestTopicId = $best->keys()->first();

        return $bestTopicId === $currentTopic->id ? null : $topics->firstWhere('id', $bestTopicId);
    }

    /**
     * @return Collection<int, float> topic_id => score
     */
    private function scoreAll(string $text, Collection $topics): Collection
    {
        if ($topics->isEmpty()) {
            return collect();
        }

        $questionWords = collect($this->tokenize($text))->unique();

        if ($questionWords->isEmpty()) {
            return collect();
        }

        $topicWordSets = $topics->mapWithKeys(
            fn (CourseTopic $topic) => [$topic->id => collect($this->tokenize($topic->title.' '.$topic->description))->unique()]
        );

        $documentFrequency = $topicWordSets->flatten()->countBy();

        return $topics->mapWithKeys(function (CourseTopic $topic) use ($questionWords, $topicWordSets, $documentFrequency) {
            $shared = $questionWords->intersect($topicWordSets[$topic->id]);

            return [$topic->id => $shared->sum(fn (string $word) => 1 / $documentFrequency[$word])];
        });
    }

    /**
     * @return array<int, string>
     */
    private function tokenize(string $text): array
    {
        preg_match_all('/[a-z0-9]+/', strtolower($text), $matches);

        return collect($matches[0])
            ->filter(fn (string $word) => strlen($word) >= 3 && ! in_array($word, self::STOPWORDS, true))
            ->values()
            ->all();
    }
}
