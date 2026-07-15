<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Complaint;
use App\Models\CourseTopic;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $summary = [
            'students' => User::where('role', 'student')->count(),
            'lecturers' => User::where('role', 'lecturer')->count(),
            'topics' => CourseTopic::count(),
            'questions' => Question::count(),
            'unanswered_questions' => Question::doesntHave('answers')->count(),
            'answers' => Answer::count(),
            'pending_complaints' => Complaint::where('status', 'pending')->count(),
        ];

        $topAskers = User::where('role', 'student')
            ->whereHas('questions')
            ->withCount('questions')
            ->orderByDesc('questions_count')
            ->take(5)
            ->get();

        $topAnswerers = User::whereIn('role', ['student', 'lecturer'])
            ->whereHas('answers')
            ->withCount('answers')
            ->orderByDesc('answers_count')
            ->take(5)
            ->get();

        $topTopics = CourseTopic::with('lecturer')
            ->whereHas('subscribers')
            ->withCount('subscribers')
            ->orderByDesc('subscribers_count')
            ->take(5)
            ->get();

        $topLecturers = User::where('role', 'lecturer')
            ->whereHas('assignedTopics')
            ->withCount('assignedTopics')
            ->orderByDesc('assigned_topics_count')
            ->take(5)
            ->get();

        return view('pages.dashboards.admin.analytics.index', compact(
            'user',
            'summary',
            'topAskers',
            'topAnswerers',
            'topTopics',
            'topLecturers'
        ));
    }
}
