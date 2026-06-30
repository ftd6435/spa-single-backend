<?php

use App\Modules\Settings\Controllers\CategoryController;
use App\Modules\Settings\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/admin')->group(function () {
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);

    Route::get('/tags/{tag}', [TagController::class, 'show']);
    Route::get('/tags', [TagController::class, 'index']);
});

// Define API routes for Settings module here
Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::get('/categories/{id}/category', [CategoryController::class, 'switchStatus']);
    Route::apiResource('categories', CategoryController::class)->except(['show', 'index']);



    Route::get('/tags/{id}/tag', [TagController::class, 'switchStatus']);
    Route::apiResource('tags', TagController::class)->except(['show', 'index']);
});
