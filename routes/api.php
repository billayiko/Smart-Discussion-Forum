<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\TopicController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public Authentication Endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Endpoints (desktop client)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/topics', [TopicController::class, 'index']);
    Route::get('/topics/{topic}/questions', [TopicController::class, 'questions']);

    Route::get('/questions/{question}', [QuestionController::class, 'show']);
    Route::post('/questions', [QuestionController::class, 'store']);
    Route::post('/questions/{question}/answers', [QuestionController::class, 'storeAnswer']);

    Route::get('/conversations', [MessageController::class, 'index']);
    Route::get('/conversations/{conversation}', [MessageController::class, 'show']);
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'storeMessage']);
    Route::post('/conversations/start', [MessageController::class, 'start']);
    Route::get('/conversation-contacts', [MessageController::class, 'contacts']);

    Route::middleware('role:admin')->group(function () {
        Route::get('/analytics', [AnalyticsController::class, 'index']);
        Route::get('/analytics/topics/{topic}', [AnalyticsController::class, 'show']);
    });
});