<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function show(Request $request, Question $question)
    {
        $user = $request->user();

        $question->load(['user', 'topic', 'answers.user', 'answers.excludedUsers']);

        $question->setRelation(
            'answers',
            $question->answers->reject(fn ($answer) => $answer->isExcludedFor($user))->values()
        );

        return response()->json($question);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'course_topic_id' => ['nullable', 'exists:course_topics,id'],
        ]);

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

        return response()->json($answer->load('user'), 201);
    }
}
