<?php

namespace App\Console\Commands;

use App\Models\ModerationSetting;
use App\Models\User;
use Illuminate\Console\Command;

class CheckMemberInactivity extends Command
{
    protected $signature = 'members:check-inactivity';

    protected $description = 'Warn and blacklist members who have not communicated in a while, based on admin-configured thresholds.';

    public function handle(): void
    {
        $settings = ModerationSetting::current();
        $now = now();

        $this->liftExpiredBlacklists($now);

        $members = User::whereIn('role', ['student', 'lecturer'])
            ->where('blacklisted', false)
            ->get();

        foreach ($members as $member) {
            $this->processMember($member, $settings, $now);
        }
    }

    protected function processMember(User $member, ModerationSetting $settings, $now): void
    {
        $lastActive = $member->last_communication_at ?? $member->created_at;

        if ($member->warning_count === 0) {
            if ($lastActive->diffInDays($now) >= $settings->inactivity_threshold_days) {
                $member->forceFill(['warning_count' => 1, 'last_warned_at' => $now])->save();
            }

            return;
        }

        if ($member->warning_count === 1) {
            if ($member->last_warned_at && $member->last_warned_at->diffInDays($now) >= $settings->inactivity_threshold_days) {
                $member->forceFill(['warning_count' => 2, 'last_warned_at' => $now])->save();
            }

            return;
        }

        if ($member->warning_count >= 2 && $member->last_warned_at
            && $member->last_warned_at->diffInDays($now) >= $settings->compliance_days) {
            $member->forceFill([
                'blacklisted' => true,
                'blacklisted_until' => $now->copy()->addDays($settings->blacklist_duration_days),
            ])->save();
        }
    }

    protected function liftExpiredBlacklists($now): void
    {
        User::where('blacklisted', true)
            ->whereNotNull('blacklisted_until')
            ->where('blacklisted_until', '<=', $now)
            ->get()
            ->each(fn (User $member) => $member->liftBlacklist());
    }
}
