<?php

use App\Actions\Teams\CreateTeam;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

/**
 * Provisions a default admin account so one exists on every environment
 * (including a fresh Render deploy) without needing shell/tinker access.
 *
 * Set DEFAULT_ADMIN_PASSWORD in the environment before this runs in
 * production — the fallback below is committed to a public repo and must
 * not be relied on outside local development.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (User::where('email', 'magin2@gmail.com')->exists()) {
            return;
        }

        $user = User::create([
            'name' => 'Magin Admin',
            'email' => 'magin2@gmail.com',
            'password' => env('DEFAULT_ADMIN_PASSWORD', 'Z8JhshlryDCAUYuB4ztqD9r'),
            'role' => 'admin',
        ]);

        app(CreateTeam::class)->handle($user, $user->name."'s Team", isPersonal: true);
    }

    public function down(): void
    {
        User::where('email', 'magin2@gmail.com')->delete();
    }
};
