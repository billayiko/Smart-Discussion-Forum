<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public Authentication Endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);