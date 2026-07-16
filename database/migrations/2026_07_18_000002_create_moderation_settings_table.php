<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moderation_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('inactivity_threshold_days')->default(30);
            $table->unsignedInteger('compliance_days')->default(7);
            $table->unsignedInteger('blacklist_duration_days')->default(30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_settings');
    }
};
