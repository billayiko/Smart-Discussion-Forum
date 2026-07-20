<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('course_topic_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->boolean('proctored')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_topic_id');
            $table->dropColumn('proctored');
        });
    }
};
