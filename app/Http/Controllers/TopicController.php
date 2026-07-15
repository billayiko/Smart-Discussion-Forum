<?php

namespace App\Http\Controllers;

use App\Models\CourseTopic;
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
