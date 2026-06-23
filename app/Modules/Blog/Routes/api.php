<?php

use App\Modules\Blog\Controllers\ArticleController;
use App\Modules\Blog\Controllers\CommentController;
use Illuminate\Support\Facades\Route;


// Routes PUBLIQUES (accessibles sans authentification)
Route::prefix('v1')->group(function () {
    Route::get('/articles/{article}/comments', [CommentController::class, 'index']);
    Route::post('/articles/{article}/comments', [CommentController::class, 'store']);
});


Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::apiResource('articles', ArticleController::class);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
});