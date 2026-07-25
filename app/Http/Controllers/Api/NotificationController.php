<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Notifications\MembershipBlacklisted;
use App\Notifications\MembershipWarning;
use App\Notifications\QuestionAnswered;
use App\Notifications\QuizScheduled;
use App\Notifications\TopicSuggested;
use Illuminate\Http\Request;

/** JSON mirror of NotificationController + _notification-bell.blade.php's data for the desktop client. */
class NotificationController extends Controller
{
    private const TYPE_KEYS = [
        QuestionAnswered::class => 'question_answered',
        QuizScheduled::class => 'quiz_scheduled',
        MembershipWarning::class => 'membership_warning',
        MembershipBlacklisted::class => 'membership_blacklisted',
        TopicSuggested::class => 'topic_suggested',
    ];

    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->take(30)->get();

        return response()->json([
            'unread_count' => $notifications->whereNull('read_at')->count(),
            'notifications' => $notifications->map(fn ($n) => [
                'id' => $n->id,
                'type' => self::TYPE_KEYS[$n->type] ?? 'other',
                'data' => $n->data,
                'read' => $n->read_at !== null,
                'created_at' => $n->created_at->toIso8601String(),
            ]),
        ]);
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked read.']);
    }

    public function markRead(Request $request, string $notification)
    {
        $request->user()->notifications()->findOrFail($notification)->markAsRead();

        return response()->json(['message' => 'Notification marked read.']);
    }
}
