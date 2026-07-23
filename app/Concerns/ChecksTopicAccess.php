<?php

namespace App\Concerns;

use App\Models\CourseTopic;
use App\Models\User;

trait ChecksTopicAccess
{
    protected function ensureTopicAccessible(User $user, CourseTopic $topic): void
    {
        if ($user->role === 'admin') {
            return;
        }

        if ($user->role === 'student') {
            abort_unless($user->subscribedTopics()->where('course_topics.id', $topic->id)->exists(), 403);

            return;
        }

        abort_unless($topic->lecturer_id === $user->id, 403);
    }
}
