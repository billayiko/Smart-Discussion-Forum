<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('participation_criteria', function (Blueprint $table) {
            $table->unsignedInteger('target_points')->default(40)->after('points_per_like_received');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participation_criteria', function (Blueprint $table) {
            $table->dropColumn('target_points');
        });
    }
};
