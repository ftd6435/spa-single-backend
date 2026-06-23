<?php

use App\Modules\Offer\Controllers\OfferController;
use App\Modules\Offer\Controllers\OfferTypeController;
use Illuminate\Support\Facades\Route;

// Routes du module Offer — toutes protégées par Sanctum (token requis)
Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::apiResource('offer-types', OfferTypeController::class); // index, store, show, update, destroy
    Route::apiResource('offers', OfferController::class);
});