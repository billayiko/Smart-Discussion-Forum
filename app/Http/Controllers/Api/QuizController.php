<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseTopic;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Notifications\QuizScheduled;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

/** JSON mirror of QuizController for the desktop client. */
class QuizController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Quiz::class);

        $user = $request->user();

        $quizzes = $user->quizzes()->withCount('questions')->latest()->get()->map(fn (Quiz $quiz) => $this->toListItem($quiz));

        return response()->json([
            'stats' => $this->lecturerCardStats($user),
            'quizzes' => $quizzes,
        ]);
    }

    public function formTopics()
    {
        return response()->json(CourseTopic::orderBy('title')->get(['id', 'title']));
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

        $quiz = $request->user()->quizzes()->create($validated);

        return response()->json(['id' => $quiz->id], 201);
    }

    public function edit(Request $request, Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        abort_unless($quiz->isEditable(), 403, 'This quiz can no longer be edited once its scheduled time has passed.');

        return response()->json([
            'id' => $quiz->id,
            'title' => $quiz->title,
            'subject' => $quiz->subject,
            'total_questions' => $quiz->total_questions,
            'scheduled_at' => $quiz->scheduled_at?->toIso8601String(),
            'duration_minutes' => $quiz->duration_minutes,
            'status' => $quiz->status,
            'course_topic_id' => $quiz->course_topic_id,
            'proctored' => $quiz->proctored,
            'is_finalized' => $quiz->isFinalized(),
        ]);
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
            $validated['total_questions'] = $quiz->total_questions;
        }

        $validated['proctored'] = (bool) ($validated['proctored'] ?? false);

        $quiz->update($validated);

        return response()->json(['message' => 'Quiz details updated.']);
    }

    public function questionsBuilder(Request $request, Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        $quiz->load('questions');

        return response()->json([
            'id' => $quiz->id,
            'title' => $quiz->title,
            'subject' => $quiz->subject,
            'duration_minutes' => $quiz->duration_minutes,
            'scheduled_at' => $quiz->scheduled_at?->toIso8601String(),
            'total_questions' => $quiz->total_questions,
            'questions_finalized_at' => $quiz->questions_finalized_at?->toIso8601String(),
            'questions' => $quiz->questions->map(fn (QuizQuestion $q) => [
                'id' => $q->id,
                'question' => $q->question,
                'option_a' => $q->option_a,
                'option_b' => $q->option_b,
                'option_c' => $q->option_c,
                'option_d' => $q->option_d,
                'correct_option' => $q->correct_option,
            ]),
        ]);
    }

    public function storeQuestion(Request $request, Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        if ($quiz->hasEnoughQuestions()) {
            return response()->json(['message' => 'This quiz already has its required number of questions.'], 422);
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

        $question = $quiz->questions()->create($validated);

        return response()->json(['id' => $question->id], 201);
    }

    public function destroyQuestion(Quiz $quiz, QuizQuestion $question)
    {
        $this->authorize('update', $quiz);

        abort_unless($question->quiz_id === $quiz->id, 404);

        if ($quiz->isFinalized()) {
            return response()->json(['message' => "This quiz's questions are already saved and cannot be changed."], 422);
        }

        $question->delete();

        return response()->json(['message' => 'Question removed.']);
    }

    public function finalizeQuestions(Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        if (! $quiz->hasEnoughQuestions()) {
            return response()->json(['message' => 'Add all '.$quiz->total_questions.' question(s) before saving this quiz.'], 422);
        }

        $quiz->markQuestionsFinalized();

        $this->announceQuiz($quiz);

        return response()->json(['message' => 'Quiz questions saved.']);
    }

    /**
     * Student-facing quiz screen data. Only reachable while the quiz's
     * scheduled window is open, and only once per student.
     */
    public function take(Request $request, Quiz $quiz)
    {
        $user = $request->user();

        if (QuizAttempt::where('quiz_id', $quiz->id)->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'You have already submitted this quiz.', 'already_attempted' => true], 409);
        }

        abort_unless($quiz->isTargetedAt($user), 403, 'This quiz is not available to you.');

        if (! $quiz->isLive()) {
            return response()->json(['message' => 'This quiz is not currently open.'], 422);
        }

        $quiz->load('questions');

        return response()->json([
            'id' => $quiz->id,
            'title' => $quiz->title,
            'subject' => $quiz->subject,
            'duration_minutes' => $quiz->duration_minutes,
            'proctored' => $quiz->proctored,
            'ends_at' => $quiz->endsAt()?->toIso8601String(),
            'questions' => $quiz->questions->map(fn (QuizQuestion $q) => [
                'id' => $q->id,
                'question' => $q->question,
                'option_a' => $q->option_a,
                'option_b' => $q->option_b,
                'option_c' => $q->option_c,
                'option_d' => $q->option_d,
            ]),
        ]);
    }

    public function submit(Request $request, Quiz $quiz)
    {
        $user = $request->user();

        if (QuizAttempt::where('quiz_id', $quiz->id)->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Already submitted.'], 200);
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
            if ((string) ($answers[$question->id] ?? null) === $question->correct_option) {
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

        return response()->json(['message' => 'Quiz submitted.', 'score' => $score, 'total' => $questions->count()], 201);
    }

    public function result(Request $request, Quiz $quiz)
    {
        $user = $request->user();

        abort_unless($quiz->hasStarted(), 403, 'This quiz has not started yet.');

        $attempt = QuizAttempt::where('quiz_id', $quiz->id)->where('user_id', $user->id)->first();

        if (! $attempt && $user->role === 'student' && $quiz->isLive()) {
            return response()->json(['message' => 'Take the quiz first.', 'redirect_to_take' => true], 409);
        }

        $quiz->load('questions');

        $attempts = QuizAttempt::where('quiz_id', $quiz->id)->with('user')->get();

        return response()->json([
            'id' => $quiz->id,
            'title' => $quiz->title,
            'subject' => $quiz->subject,
            'marks_confirmed' => $quiz->marksConfirmed(),
            'marks_confirmed_at' => $quiz->marks_confirmed_at?->toIso8601String(),
            'can_confirm' => $request->user()->can('update', $quiz),
            'questions' => $quiz->questions->map(fn (QuizQuestion $q) => [
                'id' => $q->id,
                'question' => $q->question,
                'option_a' => $q->option_a,
                'option_b' => $q->option_b,
                'option_c' => $q->option_c,
                'option_d' => $q->option_d,
                'correct_option' => $q->correct_option,
            ]),
            'attempt' => $attempt ? [
                'score' => $attempt->score,
                'total' => $attempt->total,
                'answers' => (object) ($attempt->answers ?: []),
                'proctoring_violations' => $attempt->proctoring_violations,
            ] : null,
            'report' => [
                'attempts_count' => $attempts->count(),
                'average_score_percent' => $attempts->isNotEmpty()
                    ? (int) round($attempts->avg(fn (QuizAttempt $a) => $a->total > 0 ? ($a->score / $a->total) * 100 : 0))
                    : null,
                'top_scorers' => $attempts->sortByDesc('score')->take(5)->values()->map(fn (QuizAttempt $a) => [
                    'user_name' => $a->user->name,
                    'score' => $a->score,
                    'total' => $a->total,
                ]),
            ],
        ]);
    }

    public function confirmMarks(Request $request, Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        abort_unless($quiz->hasStarted(), 403, 'Marks cannot be confirmed before the quiz starts.');

        $quiz->markMarksConfirmed();

        return response()->json(['message' => 'Marks confirmed.']);
    }

    /**
     * The single quiz currently live and available to the requesting
     * student, if any — a lightweight target for the desktop client's
     * background poller (mirrors the web's RedirectToLiveQuiz middleware
     * plus client-side setTimeout watcher, which together give the
     * "interrupt from anywhere the moment it goes live" effect).
     */
    public function liveForStudent(Request $request)
    {
        $liveQuiz = Quiz::liveFor($request->user());

        return response()->json([
            'quiz' => $liveQuiz ? ['id' => $liveQuiz->id, 'title' => $liveQuiz->title] : null,
        ]);
    }

    protected function toListItem(Quiz $quiz): array
    {
        return [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'subject' => $quiz->subject,
            'stage' => $quiz->stage(),
            'questions_count' => $quiz->questions_count,
            'total_questions' => $quiz->total_questions,
            'scheduled_at' => $quiz->scheduled_at?->toIso8601String(),
            'duration_minutes' => $quiz->duration_minutes,
            'has_started' => $quiz->hasStarted(),
            'is_editable' => $quiz->isEditable(),
            'is_finalized' => $quiz->isFinalized(),
            'marks_confirmed' => $quiz->marksConfirmed(),
        ];
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

    protected function lecturerCardStats(User $user): array
    {
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
