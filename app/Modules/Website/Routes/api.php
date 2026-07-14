<?php

use App\Modules\Website\Controllers\ClientController;
use App\Modules\Website\Controllers\PartnerController;
use App\Modules\Website\Controllers\ProjectController;
use App\Modules\Website\Controllers\ServiceController;
use App\Modules\Website\Controllers\StatisticController;
use App\Modules\Website\Controllers\TestimonialController;
use App\Modules\Website\Controllers\VisionController;
use Illuminate\Support\Facades\Route;

// Define API routes for Website module here
Route::get('v1/clients', [ClientController::class, 'publicIndex']);
Route::get('v1/clients/{id}', [ClientController::class, 'publicShow'])->whereNumber('id');
Route::get('v1/partners', [PartnerController::class, 'publicIndex']);
Route::get('v1/partners/{id}', [PartnerController::class, 'publicShow'])->whereNumber('id');
Route::get('v1/projects', [ProjectController::class, 'publicIndex']);
Route::get('v1/projects/{id}', [ProjectController::class, 'publicShow']);
Route::get('v1/services', [ServiceController::class, 'publicIndex']);
Route::get('v1/services/{id}', [ServiceController::class, 'publicShow'])->whereNumber('id');
Route::get('v1/testimonials', [TestimonialController::class, 'publicIndex']);
Route::get('v1/visions', [VisionController::class, 'publicIndex']);
Route::get('v1/statistics', [StatisticController::class, 'publicIndex']);

Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::get('clients/{id}/status', [ClientController::class, 'switchStatus']);
    Route::get('partners/{id}/status', [PartnerController::class, 'switchStatus']);
    Route::get('projects/{id}/status', [ProjectController::class, 'switchStatus']);
    Route::get('services/{id}/status', [ServiceController::class, 'switchStatus']);
    Route::get('statistics/{id}/status', [StatisticController::class, 'switchStatus']);
    Route::get('testimonials/{id}/status', [TestimonialController::class, 'switchStatus']);
    Route::get('visions/{id}/status', [VisionController::class, 'switchStatus']);

    Route::apiResource('clients', ClientController::class);
    Route::apiResource('partners', PartnerController::class);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('statistics', StatisticController::class);
    Route::apiResource('testimonials', TestimonialController::class);
    Route::apiResource('visions', VisionController::class);
});
