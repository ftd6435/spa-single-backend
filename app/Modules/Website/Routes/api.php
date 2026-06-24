<?php

use App\Modules\Website\Controllers\PartnerController;
use Illuminate\Support\Facades\Route;

// Define API routes for Website module here
Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::apiResource('partners', PartnerController::class);
});