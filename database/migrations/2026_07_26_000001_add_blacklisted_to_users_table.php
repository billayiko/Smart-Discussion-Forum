<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 2026_07_18_000001_add_moderation_columns_to_users_table placed
 * last_communication_at "after('blacklisted')" but no migration ever
 * created a blacklisted column, even though User::casts() and
 * Admin\MemberController@blacklist both read/write it — breaking any
 * blacklist/unblacklist action with "no such column: blacklisted".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('blacklisted')->default(false)->after('security_question');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('blacklisted');
        });
    }
};
