<?php

use App\Modules\Offer\Controllers\OfferController;
use App\Modules\Offer\Controllers\OfferTypeController;
use Illuminate\Support\Facades\Route;

// Routes PUBLIQUES — les visiteurs peuvent consulter les offres (page tarifs)
Route::prefix('v1')->group(function () {
    Route::get('/offers', [OfferController::class, 'index']);
    Route::get('/offers/{id}', [OfferController::class, 'show']);
});

// Routes ADMIN — protégées par Sanctum (token requis)
Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::patch('/offer-types/{id}/switch-status', [OfferTypeController::class, 'switchStatus']);
    Route::apiResource('offer-types', OfferTypeController::class);

    Route::post('/offers', [OfferController::class, 'store']);
    Route::put('/offers/{id}', [OfferController::class, 'update']);
    Route::patch('/offers/{id}/switch-status', [OfferController::class, 'switchStatus']);
    Route::delete('/offers/{id}', [OfferController::class, 'destroy']);
});
