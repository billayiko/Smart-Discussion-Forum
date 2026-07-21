<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseTopic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $topics = CourseTopic::withCount(['questions', 'subscribers'])->orderBy('title')->get();
        } else {
            $topicsQuery = $user->role === 'student' ? $user->subscribedTopics() : $user->assignedTopics();
            $topics = $topicsQuery->withCount(['questions', 'subscribers'])->orderBy('title')->get();
        }

        return response()->json($topics);
    }

    public function questions(CourseTopic $topic)
    {
        $questions = $topic->questions()
            ->with('user')
            ->withCount('answers')
            ->orderByDesc('updated_at')
            ->get();

        return response()->json($questions);
    }
}
