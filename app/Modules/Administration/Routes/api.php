<?php

use App\Modules\Administration\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Define API routes for Administration module here
Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->prefix('v1/auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});
