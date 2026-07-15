<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseTopic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TopicController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $topics = CourseTopic::with('lecturer')->withCount('subscribers')->latest()->get();
        $lecturers = User::where('role', 'lecturer')->orderBy('name')->get();

        return view('pages.dashboards.admin.topics', compact('user', 'topics', 'lecturers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'lecturer_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'lecturer')],
        ]);

        CourseTopic::create($validated);

        return back()->with('success', 'Topic created successfully.');
    }

    public function assign(Request $request, CourseTopic $topic)
    {
        $validated = $request->validate([
            'lecturer_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'lecturer')],
        ]);

        $topic->update(['lecturer_id' => $validated['lecturer_id'] ?? null]);

        $message = $topic->lecturer_id
            ? 'Topic assigned successfully.'
            : 'Lecturer removed from topic.';

        return back()->with('success', $message);
    }

    public function destroy(CourseTopic $topic)
    {
        $topic->delete();

        return back()->with('success', 'Topic removed successfully.');
    }
}
