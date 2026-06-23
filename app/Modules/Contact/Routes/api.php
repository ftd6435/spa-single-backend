<?php

use App\Modules\Contact\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

// Define API routes for Contact module here

// Route PUBLIQUE : envoyer un message depuis le formulaire de contact
Route::prefix('v1')->group(function () {
    Route::post('/contacts', [ContactController::class, 'store']);
});

// Routes ADMIN : consulter et gérer les messages reçus
Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::get('/contacts/{id}', [ContactController::class, 'show']);
    Route::delete('/contacts/{id}', [ContactController::class, 'destroy']);
});