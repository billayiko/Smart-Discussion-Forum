<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * warning_count is read/written throughout the moderation feature
 * (CheckMemberInactivity, Admin\MemberController, User::recordCommunication)
 * but no earlier migration ever added the column — this was silently
 * breaking any write to a user row (including sending a chat message, which
 * calls recordCommunication()) with "no such column: warning_count".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('warning_count')->default(0)->after('blacklisted_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('warning_count');
        });
    }
};
