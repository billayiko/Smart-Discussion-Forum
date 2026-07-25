<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;

/** JSON mirror of QuestionController::index()'s admin branch (pages.dashboards.admin.questions.index). */
class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $questions = Question::with(['user', 'topic'])
            ->withCount('answers')
            ->orderByRaw('answers_count = 0 desc')
            ->latest()
            ->get();

        return response()->json([
            'unanswered_count' => $questions->where('answers_count', 0)->count(),
            'questions' => $questions->map(fn (Question $question) => [
                'id' => $question->id,
                'title' => $question->title,
                'topic_title' => $question->topic->title ?? 'Other',
                'user_name' => $question->user->name,
                'answers_count' => $question->answers_count,
                'flagged_off_topic' => (bool) $question->flagged_off_topic,
            ]),
        ]);
    }
}
