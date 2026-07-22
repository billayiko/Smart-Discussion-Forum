<?php

use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\ComplaintController as AdminComplaintController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Admin\TopicController as AdminTopicController;
use App\Http\Controllers\Auth\SecurityQuestionPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Lecturer\StudentController as LecturerStudentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\TopicController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])
        ->whereIn('provider', ['google', 'github'])
        ->name('social.redirect');
    Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])
        ->whereIn('provider', ['google', 'github'])
        ->name('social.callback');

    Route::get('/forgot-password', [SecurityQuestionPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [SecurityQuestionPasswordController::class, 'verify'])->name('password.verify');
    Route::get('/reset-password', [SecurityQuestionPasswordController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [SecurityQuestionPasswordController::class, 'update'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'edit'])->name('onboarding.edit');
    Route::patch('/onboarding', [OnboardingController::class, 'update'])->name('onboarding.update');

    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
});

Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/student-dashboard', [DashboardController::class, 'student'])->name('student.dashboard');
    Route::get('/topics', [TopicController::class, 'index'])->name('topics.index');
    Route::post('/topics/{topic}/subscribe', [TopicController::class, 'subscribe'])->name('topics.subscribe');
    Route::delete('/topics/{topic}/subscribe', [TopicController::class, 'unsubscribe'])->name('topics.unsubscribe');
    Route::get('/quizzes/{quiz}/take', [QuizController::class, 'take'])->name('quizzes.take');
    Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit'])->name('quizzes.submit');
    Route::get('/quizzes/{quiz}/result', [QuizController::class, 'result'])->name('quizzes.result');
});

Route::middleware(['auth', 'role:lecturer'])->group(function () {
    Route::get('/lecturer-dashboard', [DashboardController::class, 'lecturer'])->name('lecturer.dashboard');
    Route::get('/lecturer/students', [LecturerStudentController::class, 'index'])->name('lecturer.students');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin-dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    Route::get('/admin/topics', [AdminTopicController::class, 'index'])->name('admin.topics.index');
    Route::post('/admin/topics', [AdminTopicController::class, 'store'])->name('admin.topics.store');
    Route::patch('/admin/topics/{topic}/assign', [AdminTopicController::class, 'assign'])->name('admin.topics.assign');
    Route::delete('/admin/topics/{topic}', [AdminTopicController::class, 'destroy'])->name('admin.topics.destroy');
    Route::get('/admin/complaints', [AdminComplaintController::class, 'index'])->name('admin.complaints.index');
    Route::patch('/admin/complaints/{complaint}', [AdminComplaintController::class, 'update'])->name('admin.complaints.update');
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    Route::get('/admin/analytics', [AdminAnalyticsController::class, 'index'])->name('admin.analytics.index');
    Route::get('/admin/members', [AdminMemberController::class, 'index'])->name('admin.members.index');
    Route::patch('/admin/members/settings', [AdminMemberController::class, 'updateSettings'])->name('admin.members.settings');
    Route::patch('/admin/members/{member}/role', [AdminMemberController::class, 'updateRole'])->name('admin.members.role');
    Route::post('/admin/members/{member}/warn', [AdminMemberController::class, 'warn'])->name('admin.members.warn');
    Route::post('/admin/members/{member}/blacklist', [AdminMemberController::class, 'blacklist'])->name('admin.members.blacklist');
    Route::post('/admin/members/{member}/unblacklist', [AdminMemberController::class, 'unblacklist'])->name('admin.members.unblacklist');
});

Route::middleware(['auth', 'role:student,lecturer,admin'])->group(function () {
    Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
    Route::get('/questions/{question}', [QuestionController::class, 'show'])->name('questions.show');
    Route::get('/topics/{topic}/discussions', [TopicController::class, 'show'])->name('topics.show');
});

Route::middleware(['auth', 'role:student,lecturer'])->group(function () {
    Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::post('/questions/{question}/answers', [QuestionController::class, 'storeAnswer'])->name('questions.answers.store');
    Route::post('/questions/{question}/complaints', [QuestionController::class, 'storeComplaint'])->name('questions.complaints.store');
    Route::post('/questions/{question}/like', [QuestionController::class, 'toggleLike'])->name('questions.like');
    Route::post('/answers/{answer}/like', [QuestionController::class, 'toggleAnswerLike'])->name('answers.like');
});

Route::middleware(['auth', 'role:student,lecturer,admin'])->group(function () {
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages/start', [MessageController::class, 'start'])->name('messages.start');
    Route::post('/messages/groups', [MessageController::class, 'storeGroup'])->name('messages.groups.store');
    Route::get('/messages/{conversation}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{conversation}/messages', [MessageController::class, 'storeMessage'])->name('messages.messages.store');
    Route::post('/messages/{conversation}/members', [MessageController::class, 'addMember'])->name('messages.members.store');
    Route::delete('/messages/{conversation}/members/{member}', [MessageController::class, 'removeMember'])->name('messages.members.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/quizzes', [QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/quizzes/create', [QuizController::class, 'create'])->name('quizzes.create');
    Route::post('/quizzes', [QuizController::class, 'store'])->name('quizzes.store');
    Route::post('/quizzes/import', [QuizController::class, 'import'])->name('quizzes.import');
    Route::get('/quizzes/{quiz}/questions', [QuizController::class, 'questionsBuilder'])->name('quizzes.questions.create');
    Route::post('/quizzes/{quiz}/questions', [QuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
    Route::post('/quizzes/{quiz}/questions/import', [QuizController::class, 'importQuestions'])->name('quizzes.questions.import');
    Route::delete('/quizzes/{quiz}/questions/{question}', [QuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');
    Route::post('/quizzes/{quiz}/finalize', [QuizController::class, 'finalizeQuestions'])->name('quizzes.questions.finalize');

    Route::livewire('invitations/{invitation}/accept', 'pages::teams.accept-invitation')->name('invitations.accept');
});

require __DIR__.'/settings.php';
