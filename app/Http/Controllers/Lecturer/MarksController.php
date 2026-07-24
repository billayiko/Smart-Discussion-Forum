<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\ParticipationCriterion;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;

class MarksController extends Controller
{
    /**
     * Each student's forum participation score and quiz average, blended
     * into one combined mark, scoped to this lecturer's own topics/quizzes.
     */
    public function index(Request $request)
    {
        $lecturer = $request->user();

        $topicIds = $lecturer->assignedTopics()->pluck('id');
        $quizIds = $lecturer->quizzes()->pluck('id');
        $questionIds = Question::whereIn('course_topic_id', $topicIds)->pluck('id');

        $criteria = ParticipationCriterion::forLecturer($lecturer);

        $questionCounts = Question::whereIn('course_topic_id', $topicIds)
            ->selectRaw('user_id, count(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $answerCounts = Answer::whereIn('question_id', $questionIds)
            ->selectRaw('user_id, count(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $likesReceived = [];

        Question::whereIn('course_topic_id', $topicIds)->withCount('likes')->get()->each(
            function (Question $question) use (&$likesReceived) {
                $likesReceived[$question->user_id] = ($likesReceived[$question->user_id] ?? 0) + $question->likes_count;
            }
        );

        Answer::whereIn('question_id', $questionIds)->withCount('likes')->get()->each(
            function (Answer $answer) use (&$likesReceived) {
                $likesReceived[$answer->user_id] = ($likesReceived[$answer->user_id] ?? 0) + $answer->likes_count;
            }
        );

        $quizAttemptsByUser = QuizAttempt::whereIn('quiz_id', $quizIds)->get()->groupBy('user_id');

        $students = User::where('role', 'student')
            ->orderBy('name')
            ->get()
            ->map(function (User $student) use ($criteria, $questionCounts, $answerCounts, $likesReceived, $quizAttemptsByUser) {
                $questions = (int) ($questionCounts[$student->id] ?? 0);
                $answers = (int) ($answerCounts[$student->id] ?? 0);
                $likes = (int) ($likesReceived[$student->id] ?? 0);

                $participationScore = $criteria->scorePercentageFor($criteria->rawPointsFor($questions, $answers, $likes));

                $attempts = $quizAttemptsByUser->get($student->id, collect());
                $quizAverage = $attempts->isNotEmpty()
                    ? (int) round($attempts->avg(fn (QuizAttempt $a) => $a->total > 0 ? ($a->score / $a->total) * 100 : 0))
                    : null;

                $combinedScore = $quizAverage !== null
                    ? (int) round(($participationScore + $quizAverage) / 2)
                    : $participationScore;

                return (object) [
                    'student' => $student,
                    'posts' => $questions + $answers,
                    'participation_score' => $participationScore,
                    'quiz_attempts' => $attempts->count(),
                    'quiz_average' => $quizAverage,
                    'combined_score' => $combinedScore,
                ];
            })
            ->sortByDesc('combined_score')
            ->values();

        return view('pages.dashboards.lecturer.marks', compact('students'));
    }
}
