<?php

namespace App\Http\Controllers;

use App\Models\CourseTopic;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Notifications\QuizScheduled;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class QuizController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Quiz::class);

        $stats = $this->lecturerCardStats();
        $quizzes = auth()->user()->quizzes()->withCount('questions')->latest()->paginate(5);

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

        $quiz = auth()->user()->quizzes()->create($validated);

        return redirect()->route('quizzes.questions.create', $quiz)
            ->with('success', 'Quiz details saved. Now add its questions.');
    }

    /**
     * Editing a published quiz's own details (not its questions) is only
     * allowed up until its scheduled start — once that passes, students may
     * already be relying on what was announced.
     */
    public function edit(Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        abort_unless($quiz->isEditable(), 403, 'This quiz can no longer be edited once its scheduled time has passed.');

        $topics = CourseTopic::orderBy('title')->get();

        return view('quizzes.edit', compact('quiz', 'topics'));
    }

    public function update(Request $request, Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        abort_unless($quiz->isEditable(), 403, 'This quiz can no longer be edited once its scheduled time has passed.');

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

        if ($quiz->isFinalized()) {
            // The question count is locked in once finalized; changing it here
            // would contradict the questions that already exist for this quiz.
            $validated['total_questions'] = $quiz->total_questions;
        }

        // An unchecked checkbox is simply absent from the request, so without
        // this the key would be missing from $validated entirely and update()
        // would silently leave a previously-true value untouched.
        $validated['proctored'] = $request->boolean('proctored');

        $quiz->update($validated);

        return redirect()->route('quizzes.index')->with('success', 'Quiz details updated.');
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

    /**
     * The question-builder screen for a single quiz: add MCQs manually,
     * import them from a CSV, and publish once the target count is reached.
     */
    public function questionsBuilder(Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        $quiz->load(['questions', 'topic']);

        return view('quizzes.questions', compact('quiz'));
    }

    public function storeQuestion(Request $request, Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        if ($quiz->hasEnoughQuestions()) {
            return back()->withErrors(['question' => 'This quiz already has its required number of questions.']);
        }

        $request->merge(['correct_option' => strtolower(trim((string) $request->input('correct_option')))]);

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'option_a' => ['required', 'string', 'max:255'],
            'option_b' => ['required', 'string', 'max:255'],
            'option_c' => ['required', 'string', 'max:255'],
            'option_d' => ['required', 'string', 'max:255'],
            'correct_option' => ['required', 'in:a,b,c,d'],
        ], [
            'correct_option.in' => 'Type a, b, c or d for the correct answer.',
        ]);

        $quiz->questions()->create($validated);

        return back()->with('success', 'Question added.');
    }

    public function importQuestions(Request $request, Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $remaining = max($quiz->total_questions - $quiz->questions()->count(), 0);

        if ($remaining === 0) {
            return back()->withErrors(['file' => 'This quiz already has its required number of questions.']);
        }

        $path = $request->file('file')->store('imports/quiz-questions', 'local');
        $contents = Storage::disk('local')->get($path);
        $rows = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $contents) ?: [])));

        if (count($rows) < 2) {
            return back()->withErrors(['file' => 'The uploaded file does not contain any question rows.']);
        }

        $header = str_getcsv($rows[0]);
        $imported = 0;

        foreach (array_slice($rows, 1) as $row) {
            if ($imported >= $remaining) {
                break;
            }

            $values = str_getcsv($row);

            if (count($values) !== count($header)) {
                continue;
            }

            $data = array_combine($header, $values);
            $correctOption = strtolower(trim((string) ($data['correct_option'] ?? '')));

            if (
                empty($data['question'])
                || empty($data['option_a']) || empty($data['option_b'])
                || empty($data['option_c']) || empty($data['option_d'])
                || ! in_array($correctOption, ['a', 'b', 'c', 'd'], true)
            ) {
                continue;
            }

            $quiz->questions()->create([
                'question' => trim((string) $data['question']),
                'option_a' => trim((string) $data['option_a']),
                'option_b' => trim((string) $data['option_b']),
                'option_c' => trim((string) $data['option_c']),
                'option_d' => trim((string) $data['option_d']),
                'correct_option' => $correctOption,
            ]);

            $imported++;
        }

        return back()->with('success', "Imported {$imported} question(s) successfully.");
    }

    public function destroyQuestion(Quiz $quiz, QuizQuestion $question)
    {
        $this->authorize('update', $quiz);

        abort_unless($question->quiz_id === $quiz->id, 404);

        if ($quiz->isFinalized()) {
            return back()->withErrors(['question' => 'This quiz\'s questions are already saved and cannot be changed.']);
        }

        $question->delete();

        return back()->with('success', 'Question removed.');
    }

    public function finalizeQuestions(Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        if (! $quiz->hasEnoughQuestions()) {
            return back()->withErrors(['quiz' => 'Add all '.$quiz->total_questions.' question(s) before saving this quiz.']);
        }

        $quiz->markQuestionsFinalized();

        $this->announceQuiz($quiz);

        return redirect()->route('quizzes.index')->with('success', 'Quiz questions saved. Students will see an announcement and be taken to it once it starts.');
    }

    /**
     * Student-facing quiz screen. Only reachable while the quiz's scheduled
     * window is open, and only once per student.
     */
    public function take(Request $request, Quiz $quiz)
    {
        $user = $request->user();

        $existingAttempt = QuizAttempt::where('quiz_id', $quiz->id)->where('user_id', $user->id)->first();

        if ($existingAttempt) {
            return redirect()->route('quizzes.result', $quiz)->with('success', 'You have already submitted this quiz.');
        }

        abort_unless($quiz->isTargetedAt($user), 403, 'This quiz is not available to you.');

        if (! $quiz->isLive()) {
            return redirect()->route('student.dashboard')->withErrors(['quiz' => 'This quiz is not currently open.']);
        }

        $quiz->load('questions');

        return view('quizzes.take', compact('quiz'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        $user = $request->user();

        if (QuizAttempt::where('quiz_id', $quiz->id)->where('user_id', $user->id)->exists()) {
            return redirect()->route('quizzes.result', $quiz);
        }

        abort_unless($quiz->isTargetedAt($user), 403, 'This quiz is not available to you.');
        abort_unless($quiz->canStillSubmit(), 403, 'This quiz is not currently open.');

        $validated = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'in:a,b,c,d'],
            'violations' => ['nullable', 'integer', 'min:0'],
        ]);

        $answers = $validated['answers'] ?? [];
        $questions = $quiz->questions;
        $score = 0;

        foreach ($questions as $question) {
            if (($answers[$question->id] ?? null) === $question->correct_option) {
                $score++;
            }
        }

        QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'score' => $score,
            'total' => $questions->count(),
            'answers' => $answers,
            'proctoring_violations' => $validated['violations'] ?? 0,
            'submitted_at' => now(),
        ]);

        return redirect()->route('quizzes.result', $quiz)->with('success', 'Quiz submitted.');
    }

    /**
     * The performance report for a quiz. Anyone whose quiz window has
     * started can see it: attendees see their own breakdown, everyone sees
     * the class-wide average and top scorers. Students who haven't attempted
     * yet, while the quiz is still open, are sent to take it instead.
     */
    public function result(Request $request, Quiz $quiz)
    {
        $user = $request->user();

        abort_unless($quiz->hasStarted(), 403, 'This quiz has not started yet.');

        $attempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $attempt && $user->role === 'student' && $quiz->isLive()) {
            return redirect()->route('quizzes.take', $quiz);
        }

        $quiz->load('questions');

        $attempts = QuizAttempt::where('quiz_id', $quiz->id)->with('user')->get();

        $report = [
            'attempts_count' => $attempts->count(),
            'average_score_percent' => $attempts->isNotEmpty()
                ? (int) round($attempts->avg(fn (QuizAttempt $a) => $a->total > 0 ? ($a->score / $a->total) * 100 : 0))
                : null,
            'top_scorers' => $attempts->sortByDesc('score')->take(5)->values(),
        ];

        return view('quizzes.result', compact('quiz', 'attempt', 'report'));
    }

    /**
     * Notify the quiz's category of students that it's been scheduled. A
     * quiz tied to a topic announces only to that topic's subscribers;
     * an untargeted quiz announces to every student.
     */
    protected function announceQuiz(Quiz $quiz): void
    {
        $students = $quiz->course_topic_id
            ? $quiz->topic->subscribers
            : User::where('role', 'student')->get();

        if ($students->isNotEmpty()) {
            Notification::send($students, new QuizScheduled($quiz));
        }
    }

    protected function lecturerCardStats(): array
    {
        $user = auth()->user();

        $activeCount = $user->quizzes()
            ->where('status', '!=', 'draft')
            ->get()
            ->filter(fn (Quiz $quiz) => in_array($quiz->stage(), ['scheduled', 'due_soon', 'active'], true))
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
