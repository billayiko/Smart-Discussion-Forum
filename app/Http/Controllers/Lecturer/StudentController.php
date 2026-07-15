<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $topics = $user->assignedTopics()->withCount('subscribers')->get();
        $topicIds = $topics->pluck('id');

        $onlineUserIds = DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes(5)->timestamp)
            ->whereNotNull('user_id')
            ->pluck('user_id');

        $students = User::where('role', 'student')
            ->with(['subscribedTopics' => function ($query) use ($topicIds) {
                $query->whereIn('course_topics.id', $topicIds);
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($student) use ($onlineUserIds) {
                $student->is_online = $onlineUserIds->contains($student->id);

                return $student;
            });

        return view('pages.dashboards.lecturer.students', compact('user', 'topics', 'students'));
    }
}
