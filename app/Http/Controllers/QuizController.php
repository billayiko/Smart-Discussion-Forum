<?php

namespace App\Http\Controllers;

use App\Models\CourseTopic;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuizController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Quiz::class);

        $stats = $this->lecturerCardStats();
        $quizzes = auth()->user()->quizzes()->latest()->paginate(10);

        return view('quizzes.index', compact('quizzes', 'stats'));
    }

    public function create()
    {
        $this->authorize('create', Quiz::class);

        $topics = CourseTopic::orderBy('title')->get();

        return view('quizzes.create', compact('topics'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Quiz::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'total_questions' => ['required', 'integer', 'min:1'],
            'scheduled_at' => ['nullable', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'status' => ['nullable', 'in:draft,planned,scheduled,due_soon,active,closed'],
            'course_topic_id' => ['nullable', 'exists:course_topics,id'],
            'proctored' => ['nullable', 'boolean'],
        ]);

        auth()->user()->quizzes()->create($validated);

        return redirect()->route('quizzes.index')->with('success', 'Quiz created successfully.');
    }

    public function import(Request $request)
    {
        $this->authorize('create', Quiz::class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls'],
        ]);

        $path = $request->file('file')->store('imports/quizzes', 'local');
        $contents = Storage::disk('local')->get($path);
        $rows = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $contents) ?: [])));

        if (count($rows) < 2) {
            return back()->withErrors(['file' => 'The uploaded file does not contain any quiz rows.']);
        }

        $header = str_getcsv($rows[0]);
        $imported = 0;

        foreach (array_slice($rows, 1) as $row) {
            $values = str_getcsv($row);

            if (count($values) !== count($header)) {
                continue;
            }

            $data = array_combine($header, $values);

            if (empty($data['title']) || empty($data['subject'])) {
                continue;
            }

            $payload = [
                'title' => trim((string) ($data['title'] ?? '')),
                'subject' => trim((string) ($data['subject'] ?? '')),
                'total_questions' => (int) ($data['total_questions'] ?? 0),
                'duration_minutes' => (int) ($data['duration_minutes'] ?? 0),
                'scheduled_at' => ! empty($data['scheduled_at']) ? now()->parse($data['scheduled_at']) : null,
                'status' => ! empty($data['status']) ? $data['status'] : 'draft',
            ];

            if ($payload['total_questions'] < 1 || $payload['duration_minutes'] < 1) {
                continue;
            }

            auth()->user()->quizzes()->create($payload);
            $imported++;
        }

        return back()->with('success', "Imported {$imported} quiz(es) successfully.");
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
