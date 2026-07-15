<?php

use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\ComplaintController as AdminComplaintController;
use App\Http\Controllers\Admin\TopicController as AdminTopicController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Lecturer\StudentController as LecturerStudentController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\TopicController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/student-dashboard', [DashboardController::class, 'student'])->name('student.dashboard');
    Route::get('/topics', [TopicController::class, 'index'])->name('topics.index');
    Route::post('/topics/{topic}/subscribe', [TopicController::class, 'subscribe'])->name('topics.subscribe');
    Route::delete('/topics/{topic}/subscribe', [TopicController::class, 'unsubscribe'])->name('topics.unsubscribe');
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
});

Route::middleware(['auth', 'role:student,lecturer,admin'])->group(function () {
    Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
    Route::get('/questions/{question}', [QuestionController::class, 'show'])->name('questions.show');
});

Route::middleware(['auth', 'role:student,lecturer'])->group(function () {
    Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::post('/questions/{question}/answers', [QuestionController::class, 'storeAnswer'])->name('questions.answers.store');
    Route::post('/questions/{question}/complaints', [QuestionController::class, 'storeComplaint'])->name('questions.complaints.store');
});

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::view('dashboard', 'dashboard')->name('dashboard');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('/quizzes', [QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/quizzes/create', [QuizController::class, 'create'])->name('quizzes.create');
    Route::post('/quizzes', [QuizController::class, 'store'])->name('quizzes.store');
    Route::post('/quizzes/import', [QuizController::class, 'import'])->name('quizzes.import');

    Route::livewire('invitations/{invitation}/accept', 'pages::teams.accept-invitation')->name('invitations.accept');
});


require __DIR__.'/settings.php';
