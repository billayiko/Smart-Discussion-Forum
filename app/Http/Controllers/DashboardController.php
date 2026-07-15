<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function student(Request $request)
    {
        $user = $request->user();

        $stats = [
            'enrolled_lectures' => 6,
            'quizzes' => Quiz::where('status', '!=', 'draft')->count(),
            'upcoming_classes' => 4,
            'average_grade' => 'A-',
        ];

        $upcomingQuizzes = Quiz::where('status', '!=', 'draft')
            ->latest('scheduled_at')
            ->take(4)
            ->get();

        [$recentQuestions, $unansweredQuestionsCount] = $this->questionsPanelData();

        return view('pages.dashboards.student', compact('user', 'stats', 'upcomingQuizzes', 'recentQuestions', 'unansweredQuestionsCount'));
    }

    public function lecturer(Request $request)
    {
        $user = $request->user();

        $stats = [
            'quizzes' => Quiz::count(),
            'active_quizzes' => Quiz::whereIn('status', ['scheduled', 'due_soon', 'active'])->count(),
            'published_this_week' => Quiz::where('status', '!=', 'draft')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'students' => 842,
        ];

        $recentQuizzes = Quiz::latest()->take(5)->get();

        [$recentQuestions, $unansweredQuestionsCount] = $this->questionsPanelData();

        return view('pages.dashboards.lecturer', compact('user', 'stats', 'recentQuizzes', 'recentQuestions', 'unansweredQuestionsCount'));
    }

    public function admin(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_quizzes' => Quiz::count(),
            'published_quizzes' => Quiz::where('status', '!=', 'draft')->count(),
            'total_attempts' => 1248,
            'average_score' => '72%',
        ];

        $quizzes = Quiz::latest()->take(5)->get();

        return view('pages.dashboards.admin', compact('user', 'stats', 'quizzes'));
    }

    protected function questionsPanelData(): array
    {
        $recentQuestions = Question::with(['user', 'topic'])
            ->withCount('answers')
            ->orderByRaw('answers_count = 0 desc')
            ->latest()
            ->take(4)
            ->get();

        $unansweredQuestionsCount = Question::doesntHave('answers')->count();

        return [$recentQuestions, $unansweredQuestionsCount];
    }
}
