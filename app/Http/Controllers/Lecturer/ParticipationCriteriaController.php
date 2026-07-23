<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\ParticipationCriterion;
use Illuminate\Http\Request;

class ParticipationCriteriaController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'points_per_question' => ['required', 'integer', 'min:0', 'max:100'],
            'points_per_answer' => ['required', 'integer', 'min:0', 'max:100'],
            'points_per_like_received' => ['required', 'integer', 'min:0', 'max:100'],
            'target_points' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        ParticipationCriterion::forLecturer($request->user())->update($validated);

        return back()->with('success', 'Participation criteria saved.');
    }
}
