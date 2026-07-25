<?php

namespace App\Http\Controllers\Api\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** JSON mirror of Lecturer\StudentController for the desktop client. */
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
            ->map(fn (User $student) => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'is_online' => $onlineUserIds->contains($student->id),
                'subscribed_topics' => $student->subscribedTopics->pluck('title'),
            ]);

        return response()->json([
            'topics' => $topics->map(fn ($t) => ['id' => $t->id, 'title' => $t->title, 'subscribers_count' => $t->subscribers_count]),
            'students' => $students,
        ]);
    }
}
