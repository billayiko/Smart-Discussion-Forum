<?php

namespace App\Http\Controllers;

use App\Models\CourseTopic;
use App\Models\Quiz;
use Illuminate\Http\Request;

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

        return view('pages.dashboards.questions.topic-show', compact('topic', 'user', 'groupMembers', 'siblingThreads', 'otherTopics', 'quiz', 'canAsk'));
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
}
