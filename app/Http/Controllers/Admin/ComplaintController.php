<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $complaints = Complaint::with(['question.user', 'user'])
            ->orderByRaw("status = 'pending' desc")
            ->latest()
            ->get();

        return view('pages.dashboards.admin.complaints.index', compact('complaints', 'user'));
    }

    public function update(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:dismiss,delete_question'],
        ]);

        if ($validated['action'] === 'delete_question') {
            $complaint->question->delete();

            return back()->with('success', 'Question deleted and complaint resolved.');
        }

        $complaint->update(['status' => 'dismissed']);

        return back()->with('success', 'Complaint dismissed.');
    }
}
