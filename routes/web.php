<?php

use App\Http\Controllers\QuizController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('/student-dashboard', 'pages.dashboards.student')->name('student.dashboard');
Route::view('/lecturer-dashboard', 'pages.dashboards.lecturer')->name('lecturer.dashboard');
Route::view('/admin-dashboard', 'pages.dashboards.admin')->name('admin.dashboard');

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
