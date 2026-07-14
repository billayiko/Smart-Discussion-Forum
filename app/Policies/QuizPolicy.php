<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'lecturer' || $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'lecturer' || $user->role === 'admin';
    }

    public function update(User $user, Quiz $quiz): bool
    {
        return $user->id === $quiz->user_id || $user->role === 'admin';
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $user->id === $quiz->user_id || $user->role === 'admin';
    }
}
