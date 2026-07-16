<?php

namespace App\Http\Controllers;

use App\Models\CourseTopic;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $questions = Question::with(['user', 'topic'])
            ->withCount('answers')
            ->orderByRaw('answers_count = 0 desc')
            ->latest()
            ->get();

        $topics = CourseTopic::orderBy('title')->get();
        $unansweredCount = $questions->where('answers_count', 0)->count();

        return view("pages.dashboards.{$user->role}.questions.index", compact('questions', 'topics', 'unansweredCount', 'user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'course_topic_id' => ['nullable', 'exists:course_topics,id'],
        ]);

        $request->user()->questions()->create($validated);

        return back()->with('success', 'Your question has been posted.');
    }

    public function show(Request $request, Question $question)
    {
        $user = $request->user();

        $question->load(['user', 'topic', 'answers.user']);

        return view("pages.dashboards.{$user->role}.questions.show", compact('question', 'user'));
    }

    public function storeAnswer(Request $request, Question $question)
    {
        $validated = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $question->answers()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $request->user()->recordCommunication();

        return back()->with('success', 'Your reply has been posted.');
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

        return back()->with('success', 'Your complaint has been submitted to the admin for review.');
    }

    public function destroy(Question $question)
    {
        $question->delete();

        return redirect()->route('questions.index')->with('success', 'Question deleted successfully.');
    }
}
