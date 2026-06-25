<?php

use App\Modules\Website\Controllers\ClientController;
use App\Modules\Website\Controllers\PartnerController;
use App\Modules\Website\Controllers\ProjectController;
use App\Modules\Website\Controllers\ServiceController;
use App\Modules\Website\Controllers\StatisticController;
use App\Modules\Website\Controllers\VisionController;
use Illuminate\Support\Facades\Route;

// Define API routes for Website module here
Route::get('v1/projects', [ProjectController::class, 'publicIndex']);
Route::get('v1/projects/{id}', [ProjectController::class, 'publicShow']);
Route::get('v1/visions', [VisionController::class, 'publicIndex']);

Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('partners', PartnerController::class);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('statistics', StatisticController::class);
    Route::apiResource('visions', VisionController::class);
});
