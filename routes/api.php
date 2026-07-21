<?php

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
});