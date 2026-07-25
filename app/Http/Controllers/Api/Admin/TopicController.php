<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseTopic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** JSON mirror of Admin\TopicController for the desktop client. Routes are role:admin-gated. */
class TopicController extends Controller
{
    public function index(Request $request)
    {
        $topics = CourseTopic::with('lecturer')->withCount('subscribers')->latest()->get();
        $lecturers = User::where('role', 'lecturer')->orderBy('name')->get(['id', 'name']);

        return response()->json([
            'topics' => $topics->map(fn (CourseTopic $topic) => [
                'id' => $topic->id,
                'title' => $topic->title,
                'description' => $topic->description,
                'lecturer_id' => $topic->lecturer_id,
                'lecturer_name' => $topic->lecturer?->name,
                'subscribers_count' => $topic->subscribers_count,
            ]),
            'lecturers' => $lecturers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'lecturer_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'lecturer')],
        ]);

        $topic = CourseTopic::create($validated);

        return response()->json(['message' => 'Topic created successfully.', 'id' => $topic->id], 201);
    }

    public function assign(Request $request, CourseTopic $topic)
    {
        $validated = $request->validate([
            'lecturer_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'lecturer')],
        ]);

        $topic->update(['lecturer_id' => $validated['lecturer_id'] ?? null]);

        return response()->json([
            'message' => $topic->lecturer_id ? 'Topic assigned successfully.' : 'Lecturer removed from topic.',
        ]);
    }

    public function destroy(CourseTopic $topic)
    {
        $topic->delete();

        return response()->json(['message' => 'Topic removed successfully.']);
    }
}
