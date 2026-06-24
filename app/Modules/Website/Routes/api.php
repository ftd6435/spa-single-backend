<?php

use App\Modules\Website\Controllers\PartnerController;
use App\Modules\Website\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

// Define API routes for Website module here
Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::apiResource('partners', PartnerController::class);

    Route::post('services/{service}/tags', [ServiceController::class, 'addTags']);
    Route::delete('services/{service}/tags/{tag}', [ServiceController::class, 'removeTag']);
    Route::apiResource('services', ServiceController::class);
});
