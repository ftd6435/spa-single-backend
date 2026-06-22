<?php

use App\Modules\Settings\Controllers\CategoryController;
use App\Modules\Settings\Controllers\TagController;
use Illuminate\Support\Facades\Route;

// Define API routes for Settings module here
Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::get('/categories/{id}/category', [CategoryController::class, 'switchStatus']);
    Route::apiResource('categories', CategoryController::class);

    Route::get('/tags/{id}/tag', [TagController::class, 'switchStatus']);
    Route::apiResource('tags', TagController::class);
});
