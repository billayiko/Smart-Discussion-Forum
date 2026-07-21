<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModerationSetting;
use App\Models\User;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $settings = ModerationSetting::current();

        $members = User::whereIn('role', ['student', 'lecturer'])
            ->orderByDesc('warning_count')
            ->orderBy('name')
            ->get();

        $admins = User::where('role', 'admin')->orderBy('name')->get();

        return view('pages.dashboards.admin.members.index', compact('user', 'settings', 'members', 'admins'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'inactivity_threshold_days' => ['required', 'integer', 'min:1'],
            'compliance_days' => ['required', 'integer', 'min:1'],
            'blacklist_duration_days' => ['required', 'integer', 'min:1'],
        ]);

        ModerationSetting::current()->update($validated);

        return back()->with('success', 'Moderation settings updated.');
    }

    public function updateRole(Request $request, User $member)
    {
        abort_if($member->id === $request->user()->id, 422, "You can't change your own role.");

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:student,lecturer,admin'],
        ]);

        $member->update(['role' => $validated['role']]);

        return back()->with('success', "{$member->name} is now a(n) {$member->roleLabel()}.");
    }

    public function warn(Request $request, User $member)
    {
        $member->forceFill([
            'warning_count' => min(2, $member->warning_count + 1),
            'last_warned_at' => now(),
        ])->save();

        return back()->with('success', "Warning issued to {$member->name}.");
    }

    public function blacklist(Request $request, User $member)
    {
        $settings = ModerationSetting::current();

        $member->forceFill([
            'blacklisted' => true,
            'blacklisted_until' => now()->addDays($settings->blacklist_duration_days),
        ])->save();

        return back()->with('success', "{$member->name} has been blacklisted.");
    }

    public function unblacklist(Request $request, User $member)
    {
        $member->liftBlacklist();

        return back()->with('success', "{$member->name} has been reinstated.");
    }
}
