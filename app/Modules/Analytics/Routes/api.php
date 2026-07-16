<?php

use App\Modules\Analytics\Controllers\AnalyticController;
use Illuminate\Support\Facades\Route;

// Define API routes for Analytics module here
Route::prefix('analytics')->group(function () {
    Route::get('/', [AnalyticController::class, 'index'])->middleware('auth:sanctum');

    Route::post('/track', [AnalyticController::class, 'track']);
});
