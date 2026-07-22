<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\CourseTopic;
use App\Models\Question;
use App\Models\Quiz;
use App\Notifications\QuestionAnswered;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $questions = Question::with(['user', 'topic'])
                ->withCount('answers')
                ->orderByRaw('answers_count = 0 desc')
                ->latest()
                ->get();

            $topics = CourseTopic::orderBy('title')->get();
            $unansweredCount = $questions->where('answers_count', 0)->count();

            return view("pages.dashboards.{$user->role}.questions.index", compact('questions', 'topics', 'unansweredCount', 'user'));
        }

        $topicsQuery = $user->role === 'student' ? $user->subscribedTopics() : $user->assignedTopics();
        $topics = $topicsQuery->withCount(['questions', 'subscribers'])->orderBy('title')->get();

        return view("pages.dashboards.{$user->role}.questions.index", compact('topics', 'user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'course_topic_id' => ['nullable', 'exists:course_topics,id'],
        ]);

        $request->user()->questions()->create($validated);

        return back()->with('success', 'Your question has been posted.');
    }

    public function show(Request $request, Question $question)
    {
        $user = $request->user();

        $question->load(['user', 'topic', 'answers.user', 'answers.excludedUsers', 'answers.likes', 'likes']);

        $extra = [];

        if (in_array($user->role, ['student', 'lecturer'], true)) {
            $visibleAnswers = $question->answers->reject(fn ($answer) => $answer->isExcludedFor($user))->values();

            if (! $request->hasHeader('X-Sync')) {
                // Quietly: a passive view shouldn't touch the question's
                // "last activity" via Answer's $touches (only real posts should).
                $question->incrementQuietly('views');
                $visibleAnswers->each(fn ($answer) => $answer->incrementQuietly('views'));
            }

            $question->setRelation('answers', $visibleAnswers);

            $topic = $question->topic;

            $groupMembers = collect();
            $siblingThreads = collect();

            if ($topic) {
                $groupMembers = $topic->subscribers()->get()
                    ->when($topic->lecturer, fn ($members) => $members->push($topic->lecturer))
                    ->unique('id')
                    ->values();

                $siblingThreads = $topic->questions()
                    ->withCount('answers')
                    ->orderByDesc('updated_at')
                    ->get();
            }

            $otherTopics = CourseTopic::withCount('subscribers')
                ->when($topic, fn ($query) => $query->where('id', '!=', $topic->id))
                ->orderBy('title')
                ->get();

            $quiz = $topic
                ? Quiz::where('course_topic_id', $topic->id)
                    ->whereNotIn('status', ['draft'])
                    ->where('scheduled_at', '>=', now())
                    ->orderBy('scheduled_at')
                    ->first()
                : null;

            $extra = compact('groupMembers', 'siblingThreads', 'otherTopics', 'quiz');
        }

        return view("pages.dashboards.{$user->role}.questions.show", array_merge(compact('question', 'user'), $extra));
    }

    public function storeAnswer(Request $request, Question $question)
    {
        $validated = $request->validate([
            'body' => ['required', 'string'],
            'topic' => ['nullable', 'string', 'max:255'],
            'excluded_user_ids' => ['nullable', 'array'],
            'excluded_user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $answer = $question->answers()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'topic' => $validated['topic'] ?? null,
        ]);
        $answer->setRelation('user', $request->user());

        $excludedUserIds = $validated['excluded_user_ids'] ?? [];
        $answer->excludedUsers()->sync($excludedUserIds);

        if ($question->user_id !== $request->user()->id && ! in_array($question->user_id, $excludedUserIds, true)) {
            $question->user->notify(new QuestionAnswered($answer));
        }

        $request->user()->recordCommunication();

        return back()->with('success', 'Your reply has been posted.');
    }

    public function toggleLike(Request $request, Question $question)
    {
        $liked = $question->toggleLikeFor($request->user());

        return response()->json(['liked' => $liked, 'count' => $question->likes()->count()]);
    }

    public function toggleAnswerLike(Request $request, Answer $answer)
    {
        $liked = $answer->toggleLikeFor($request->user());

        return response()->json(['liked' => $liked, 'count' => $answer->likes()->count()]);
    }

    public function storeComplaint(Request $request, Question $question)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $question->complaints()->create([
            'user_id' => $request->user()->id,
            'reason' => $validated['reason'],
        ]);

        return back()->with('success', 'Your complaint has been submitted to the admin for review.');
    }

    public function destroy(Question $question)
    {
        $question->delete();

        return redirect()->route('questions.index')->with('success', 'Question deleted successfully.');
    }
}
