<?php

use App\Models\TeamInvitation;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    TeamInvitation::query()
        ->whereNotNull('expires_at')
        ->where('expires_at', '<', now())
        ->delete();
})->daily()->description('Delete expired team invitations');

Schedule::command('members:check-inactivity')->daily()->description('Warn and blacklist inactive members');

Schedule::command('topics:generate-suggestions')->daily()->description('Suggest relevant topics to students based on peer engagement');
