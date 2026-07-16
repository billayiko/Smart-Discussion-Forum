<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_communication_at')->nullable()->after('blacklisted');
            $table->timestamp('last_warned_at')->nullable()->after('last_communication_at');
            $table->timestamp('blacklisted_until')->nullable()->after('last_warned_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_communication_at', 'last_warned_at', 'blacklisted_until']);
        });
    }
};
