<?php

use App\Modules\Blog\Controllers\ArticleController;
use App\Modules\Blog\Controllers\CommentController;
use Illuminate\Support\Facades\Route;


// Routes PUBLIQUES (accessibles sans authentification)
Route::prefix('v1')->group(function () {
    Route::get('/articles/{article}/comments', [CommentController::class, 'index']);
    Route::post('/articles/{article}/comments', [CommentController::class, 'store']);
});


// Routes ADMIN (protégées par authentification)
Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::apiResource('articles', ArticleController::class);

    // Gestion des tags d'un article (table pivot article_tag)
    Route::post('/articles/{article}/tags', [ArticleController::class, 'attachTags']);
    Route::delete('/articles/{article}/tags/{tag}', [ArticleController::class, 'detachTag']);

    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
});