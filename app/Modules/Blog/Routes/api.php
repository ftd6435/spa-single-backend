<?php

use App\Modules\Blog\Controllers\ArticleController;
use App\Modules\Blog\Controllers\ArticleImageController;
use App\Modules\Blog\Controllers\CommentController;
use Illuminate\Support\Facades\Route;

// Routes PUBLIQUES — accessibles sans authentification
Route::prefix('v1')->group(function () {
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{id}', [ArticleController::class, 'show']);

    Route::get('/articles/{article}/comments', [CommentController::class, 'index']);
    Route::post('/articles/{article}/comments', [CommentController::class, 'store']);

    // Sert les images insérées dans la description (URL stable, redirige vers R2)
    // La contrainte where évite toute tentative de path traversal (../)
    Route::get('/article-images/{image}', [ArticleImageController::class, 'show']);
});

// Routes ADMIN — protégées par Sanctum (token requis)
Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    // Lecture admin : renvoie aussi les éléments désactivés
    Route::get('/articles', [ArticleController::class, 'adminIndex']);
    Route::get('/articles/{id}', [ArticleController::class, 'adminShow'])->whereNumber('id');
    Route::get('/articles/{article}/comments', [CommentController::class, 'adminIndex']);

    Route::post('/articles', [ArticleController::class, 'store']);
    Route::put('/articles/{id}', [ArticleController::class, 'update']);
    Route::patch('/articles/{id}/switch-status', [ArticleController::class, 'switchStatus']);
    Route::delete('/articles/{id}', [ArticleController::class, 'destroy']);

    // Upload des images du contenu — appelée par l'upload adapter de CKEditor
    Route::post('/articles/content-images', [ArticleImageController::class, 'store']);
    Route::delete('/article-images/{image}', [ArticleImageController::class, 'destroy']);

    Route::patch('/comments/{id}/switch-status', [CommentController::class, 'switchStatus']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
});
