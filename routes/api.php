<?php

use App\Http\Controllers\Api\Admin\ComplaintController as AdminComplaintController;
use App\Http\Controllers\Api\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Api\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\Api\Admin\TopicController as AdminTopicController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\Lecturer\DashboardController as LecturerDashboardController;
use App\Http\Controllers\Api\Lecturer\MarksController as LecturerMarksController;
use App\Http\Controllers\Api\Lecturer\StudentController as LecturerStudentController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Api\TopicController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\EnsureApiUserIsNotBlacklisted;
use Illuminate\Support\Facades\Route;

// Public Authentication Endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public "forgot password" endpoints — by definition reachable while logged out.
Route::post('/forgot-password/verify', [PasswordResetController::class, 'verify']);
Route::post('/forgot-password/reset', [PasswordResetController::class, 'reset']);

// Authenticated Endpoints (desktop client)
Route::middleware(['auth:sanctum', EnsureApiUserIsNotBlacklisted::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/me', [ProfileController::class, 'update']);
    Route::put('/password', [ProfileController::class, 'updatePassword']);

    Route::patch('/onboarding', [OnboardingController::class, 'update']);
    Route::delete('/onboarding', [OnboardingController::class, 'decline']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);

    Route::get('/topics', [TopicController::class, 'index']);
    Route::get('/topics/{topic}/questions', [TopicController::class, 'questions']);
    Route::get('/topics/{topic}/leaderboard-and-activity', [TopicController::class, 'leaderboardAndActivity']);
    Route::get('/topics/{topic}/export-pdf', [TopicController::class, 'exportPdf']);
    Route::get('/topics/{topic}/export-participation-csv', [TopicController::class, 'exportParticipationCsv']);

    Route::get('/questions/{question}', [QuestionController::class, 'show']);
    Route::post('/questions', [QuestionController::class, 'store']);
    Route::post('/questions/{question}/answers', [QuestionController::class, 'storeAnswer']);

    Route::get('/conversations', [MessageController::class, 'index']);
    Route::get('/conversations/{conversation}', [MessageController::class, 'show']);
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'storeMessage']);
    Route::post('/conversations/start', [MessageController::class, 'start']);
    Route::post('/conversations/groups', [MessageController::class, 'storeGroup']);
    Route::post('/conversations/{conversation}/members', [MessageController::class, 'addMember']);
    Route::delete('/conversations/{conversation}/members/{member}', [MessageController::class, 'removeMember']);
    Route::get('/conversation-contacts', [MessageController::class, 'contacts']);

    // Quiz management (policy-authorized inside the controller, mirroring the
    // web routes' plain 'auth' middleware + QuizPolicy pattern).
    Route::get('/quizzes', [QuizController::class, 'index']);
    Route::get('/quizzes/topics', [QuizController::class, 'formTopics']);
    Route::post('/quizzes', [QuizController::class, 'store']);
    Route::post('/quizzes/import', [QuizController::class, 'import']);
    Route::get('/quizzes/{quiz}/edit', [QuizController::class, 'edit']);
    Route::patch('/quizzes/{quiz}', [QuizController::class, 'update']);
    Route::get('/quizzes/{quiz}/questions', [QuizController::class, 'questionsBuilder']);
    Route::post('/quizzes/{quiz}/questions', [QuizController::class, 'storeQuestion']);
    Route::post('/quizzes/{quiz}/questions/import', [QuizController::class, 'importQuestions']);
    Route::delete('/quizzes/{quiz}/questions/{question}', [QuizController::class, 'destroyQuestion']);
    Route::post('/quizzes/{quiz}/finalize', [QuizController::class, 'finalizeQuestions']);
    Route::get('/quizzes/{quiz}/result', [QuizController::class, 'result']);
    Route::post('/quizzes/{quiz}/confirm-marks', [QuizController::class, 'confirmMarks']);

    Route::middleware('role:admin')->group(function () {
        Route::get('/analytics', [AnalyticsController::class, 'index']);
        Route::get('/analytics/topics/{topic}', [AnalyticsController::class, 'show']);
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);

        Route::get('/admin/topics', [AdminTopicController::class, 'index']);
        Route::post('/admin/topics', [AdminTopicController::class, 'store']);
        Route::patch('/admin/topics/{topic}/assign', [AdminTopicController::class, 'assign']);
        Route::delete('/admin/topics/{topic}', [AdminTopicController::class, 'destroy']);

        Route::get('/admin/complaints', [AdminComplaintController::class, 'index']);
        Route::patch('/admin/complaints/{complaint}', [AdminComplaintController::class, 'update']);

        Route::get('/admin/members', [AdminMemberController::class, 'index']);
        Route::patch('/admin/members/settings', [AdminMemberController::class, 'updateSettings']);
        Route::patch('/admin/members/{member}/role', [AdminMemberController::class, 'updateRole']);
        Route::post('/admin/members/{member}/warn', [AdminMemberController::class, 'warn']);
        Route::post('/admin/members/{member}/blacklist', [AdminMemberController::class, 'blacklist']);
        Route::post('/admin/members/{member}/unblacklist', [AdminMemberController::class, 'unblacklist']);

        Route::get('/admin/questions', [AdminQuestionController::class, 'index']);
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy']);
    });

    Route::middleware('role:lecturer')->group(function () {
        Route::get('/lecturer/dashboard', [LecturerDashboardController::class, 'index']);
        Route::patch('/lecturer/participation-criteria', [LecturerDashboardController::class, 'updateParticipationCriteria']);
        Route::get('/lecturer/students', [LecturerStudentController::class, 'index']);
        Route::get('/lecturer/marks', [LecturerMarksController::class, 'index']);
    });

    Route::middleware('role:student')->group(function () {
        Route::get('/student/dashboard', [StudentDashboardController::class, 'index']);
        Route::get('/student/live-quiz', [QuizController::class, 'liveForStudent']);
        Route::get('/quizzes/{quiz}/take', [QuizController::class, 'take']);
        Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit']);

        Route::get('/topics/browse', [TopicController::class, 'browse']);
        Route::post('/topics/{topic}/subscribe', [TopicController::class, 'subscribe']);
        Route::delete('/topics/{topic}/subscribe', [TopicController::class, 'unsubscribe']);
        Route::post('/topics/{topic}/ignore-suggestion', [TopicController::class, 'ignoreSuggestion']);
    });

    Route::middleware('role:student,lecturer')->group(function () {
        Route::post('/questions/{question}/like', [QuestionController::class, 'toggleLike']);
        Route::post('/answers/{answer}/like', [QuestionController::class, 'toggleAnswerLike']);
        Route::post('/questions/{question}/complaints', [QuestionController::class, 'storeComplaint']);
    });
});
