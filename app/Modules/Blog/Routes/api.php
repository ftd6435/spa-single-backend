<?php

use App\Modules\Blog\Controllers\ArticleController;
use App\Modules\Blog\Controllers\CommentController;
use Illuminate\Support\Facades\Route;

// Routes PUBLIQUES — accessibles sans authentification
Route::prefix('v1')->group(function () {
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{id}', [ArticleController::class, 'show']);

    Route::get('/articles/{article}/comments', [CommentController::class, 'index']);
    Route::post('/articles/{article}/comments', [CommentController::class, 'store']);
});

// Routes ADMIN — protégées par Sanctum (token requis)
Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::put('/articles/{id}', [ArticleController::class, 'update']);
    Route::delete('/articles/{id}', [ArticleController::class, 'destroy']);

    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
});
