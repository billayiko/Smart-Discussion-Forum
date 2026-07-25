<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;

/** JSON mirror of Admin\ComplaintController for the desktop client. Routes are role:admin-gated. */
class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $complaints = Complaint::with(['question.user', 'user'])
            ->orderByRaw("status = 'pending' desc")
            ->latest()
            ->get();

        return response()->json($complaints->map(fn (Complaint $complaint) => [
            'id' => $complaint->id,
            'reason' => $complaint->reason,
            'status' => $complaint->status,
            'reporter_name' => $complaint->user->name,
            'question_id' => $complaint->question_id,
            'question_title' => $complaint->question?->title,
            'question_author' => $complaint->question?->user?->name,
            'created_at' => $complaint->created_at,
        ]));
    }

    public function update(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:dismiss,delete_question'],
        ]);

        if ($validated['action'] === 'delete_question') {
            $complaint->question->delete();

            return response()->json(['message' => 'Question deleted and complaint resolved.']);
        }

        $complaint->update(['status' => 'dismissed']);

        return response()->json(['message' => 'Complaint dismissed.']);
    }
}
