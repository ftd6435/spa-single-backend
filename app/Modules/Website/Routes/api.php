<?php

use App\Modules\Website\Controllers\PartnerController;
use App\Modules\Website\Controllers\ServiceController;
use App\Modules\Website\Controllers\StatisticController;
use Illuminate\Support\Facades\Route;

// Define API routes for Website module here
Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::apiResource('partners', PartnerController::class);
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('statistics', StatisticController::class);
});
