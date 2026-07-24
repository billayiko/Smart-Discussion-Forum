<?php

namespace App\Support;

use App\Models\CourseTopic;
use Illuminate\Support\Collection;

/**
 * Content-based classifier: matches a question's text against each course
 * topic's title/description by shared keywords, so a question left as
 * "Other / General" can still be auto-filed under the topic it actually
 * belongs to instead of staying uncategorized.
 *
 * Words are weighted by rarity across topics (like TF-IDF) rather than
 * counted flatly, since a topic's title/description here is often just a
 * couple of words — a single distinctive shared term (e.g. "javascript")
 * should outweigh several shared generic ones.
 */
class TopicClassifier
{
    /**
     * Minimum combined weight to trust a match over leaving the question
     * uncategorized. 1.0 is "found at least one word unique to a single
     * topic," the strongest possible single-word signal.
     */
    private const MIN_SCORE = 1.0;

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

        if ($topics->isEmpty()) {
            return null;
        }

        $questionWords = collect($this->tokenize($text))->unique();

        if ($questionWords->isEmpty()) {
            return null;
        }

        $topicWordSets = $topics->mapWithKeys(
            fn (CourseTopic $topic) => [$topic->id => collect($this->tokenize($topic->title.' '.$topic->description))->unique()]
        );

        $documentFrequency = $topicWordSets->flatten()->countBy();

        $bestTopic = null;
        $bestScore = 0.0;

        foreach ($topics as $topic) {
            $shared = $questionWords->intersect($topicWordSets[$topic->id]);
            $score = $shared->sum(fn (string $word) => 1 / $documentFrequency[$word]);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestTopic = $topic;
            }
        }

        return $bestScore >= self::MIN_SCORE ? $bestTopic : null;
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
