<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuizController extends Controller
{

    public function index()
    {
        $stats = $this->lecturerCardStats();
        $quizzes = auth()->user()->quizzes()->latest()->paginate(10);

        return view('quizzes.index', compact('quizzes', 'stats'));
    }

    public function create()
    {
        return view('quizzes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'total_questions' => ['required', 'integer', 'min:1'],
            'scheduled_at' => ['nullable', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'status' => ['nullable', 'in:draft,planned,scheduled,due_soon,active,closed'],
        ]);

        auth()->user()->quizzes()->create($validated);

        return redirect()->route('quizzes.index')->with('success', 'Quiz created successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls'],
        ]);

        $path = $request->file('file')->store('imports/quizzes', 'local');

        return back()->with('success', 'Quiz import file uploaded successfully.')->with('import_path', $path);
    }

    protected function lecturerCardStats(): array
    {
        $user = auth()->user();

        $activeCount = $user->quizzes()
            ->whereIn('status', ['scheduled', 'due_soon', 'active'])
            ->count();

        $publishedThisWeek = $user->quizzes()
            ->where('status', '!=', 'draft')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        return [
            'active_count' => $activeCount,
            'published_this_week' => $publishedThisWeek,
        ];
    }
}
