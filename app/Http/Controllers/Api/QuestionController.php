<?php

namespace App\Http\Controllers\Api;

use App\Concerns\ChecksTopicAccess;
use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\CourseTopic;
use App\Models\Question;
use App\Notifications\QuestionAnswered;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    use ChecksTopicAccess;


    public function show(Request $request, Question $question)
    {
        $user = $request->user();

        $question->load(['user', 'topic', 'answers.user', 'answers.excludedUsers', 'answers.likes', 'likes']);

        $question->setRelation(
            'answers',
            $question->answers->reject(fn ($answer) => $answer->isExcludedFor($user))->values()
        );

        $question->answers_count = $question->answers->count();
        $question->likes_count = $question->likes->count();
        $question->liked_by_me = $question->isLikedBy($user);

        $question->answers->each(function (Answer $answer) use ($user) {
            $answer->likes_count = $answer->likes->count();
            $answer->liked_by_me = $answer->isLikedBy($user);
        });

        return response()->json($question);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'course_topic_id' => ['nullable', 'exists:course_topics,id'],
        ]);

        if (! empty($validated['course_topic_id'])) {
            $this->ensureTopicAccessible($request->user(), CourseTopic::findOrFail($validated['course_topic_id']));
        }

        $question = $request->user()->questions()->create($validated);

        return response()->json($question->load(['user', 'topic']), 201);
    }

    public function storeAnswer(Request $request, Question $question)
    {
        $validated = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $answer = $question->answers()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $request->user()->recordCommunication();

        if ($question->user_id !== $request->user()->id) {
            $question->user->notify(new QuestionAnswered($answer));
        }

        return response()->json($answer->load('user'), 201);
    }

    public function toggleLike(Request $request, Question $question)
    {
        $liked = $question->toggleLikeFor($request->user());

        return response()->json(['liked' => $liked, 'count' => $question->likes()->count()]);
    }

    public function toggleAnswerLike(Request $request, Answer $answer)
    {
        $liked = $answer->toggleLikeFor($request->user());

        return response()->json(['liked' => $liked, 'count' => $answer->likes()->count()]);
    }

    public function storeComplaint(Request $request, Question $question)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $question->complaints()->create([
            'user_id' => $request->user()->id,
            'reason' => $validated['reason'],
        ]);

        return response()->json(['message' => 'Your complaint has been submitted to the admin for review.'], 201);
    }

    public function destroy(Question $question)
    {
        $question->delete();

        return response()->json(['message' => 'Question deleted successfully.']);
    }
}
