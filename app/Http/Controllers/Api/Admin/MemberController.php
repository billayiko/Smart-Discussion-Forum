<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModerationSetting;
use App\Models\User;
use App\Notifications\MembershipBlacklisted;
use App\Notifications\MembershipWarning;
use Illuminate\Http\Request;

/** JSON mirror of Admin\MemberController for the desktop client. Routes are role:admin-gated. */
class MemberController extends Controller
{
    public function index(Request $request)
    {
        $settings = ModerationSetting::current();

        $members = User::whereIn('role', ['student', 'lecturer'])
            ->orderByDesc('warning_count')
            ->orderBy('name')
            ->get();

        return response()->json([
            'settings' => [
                'inactivity_threshold_days' => $settings->inactivity_threshold_days,
                'compliance_days' => $settings->compliance_days,
                'blacklist_duration_days' => $settings->blacklist_duration_days,
            ],
            'members' => $members->map(fn (User $member) => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'role' => $member->role,
                'warning_count' => $member->warning_count,
                'blacklisted' => (bool) $member->blacklisted,
                'blacklisted_until' => $member->blacklisted_until,
            ]),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'inactivity_threshold_days' => ['required', 'integer', 'min:1'],
            'compliance_days' => ['required', 'integer', 'min:1'],
            'blacklist_duration_days' => ['required', 'integer', 'min:1'],
        ]);

        ModerationSetting::current()->update($validated);

        return response()->json(['message' => 'Moderation settings updated.']);
    }

    public function updateRole(Request $request, User $member)
    {
        abort_if($member->id === $request->user()->id, 422, "You can't change your own role.");

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:student,lecturer,admin'],
        ]);

        $member->update(['role' => $validated['role']]);

        return response()->json(['message' => "{$member->name} is now a(n) {$member->roleLabel()}."]);
    }

    public function warn(Request $request, User $member)
    {
        $settings = ModerationSetting::current();
        $warningCount = min(2, $member->warning_count + 1);

        $member->forceFill([
            'warning_count' => $warningCount,
            'last_warned_at' => now(),
        ])->save();

        $member->notify(new MembershipWarning($warningCount, $settings->inactivity_threshold_days));

        return response()->json(['message' => "Warning issued to {$member->name}."]);
    }

    public function blacklist(Request $request, User $member)
    {
        $settings = ModerationSetting::current();
        $until = now()->addDays($settings->blacklist_duration_days);

        $member->forceFill([
            'blacklisted' => true,
            'blacklisted_until' => $until,
        ])->save();

        $member->notify(new MembershipBlacklisted($until));

        return response()->json(['message' => "{$member->name} has been blacklisted."]);
    }

    public function unblacklist(Request $request, User $member)
    {
        $member->liftBlacklist();

        return response()->json(['message' => "{$member->name} has been reinstated."]);
    }
}
