<?php

use App\Modules\Formation\Controllers\FormationCategoryController;
use App\Modules\Formation\Controllers\FormationController;
use App\Modules\Formation\Controllers\FormationImageController;
use App\Modules\Formation\Controllers\ParticipantController;
use App\Modules\Formation\Controllers\ParticipationController;
use App\Modules\Formation\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/formation-categories', [FormationCategoryController::class, 'index']);
    Route::get('/formation-categories/{formationCategory}', [FormationCategoryController::class, 'show'])
        ->whereNumber('formationCategory');
    Route::get('/formations', [FormationController::class, 'index']);
    Route::get('/formations/{formation}', [FormationController::class, 'show'])->whereNumber('formation');
    Route::post('/formations/{formation}/participations', [ParticipationController::class, 'store'])
        ->whereNumber('formation');
    Route::get('/formation-images/{image}', [FormationImageController::class, 'show'])
        ->where('image', '[A-Za-z0-9\-]+\.[A-Za-z0-9]+');
});

Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::post('/formation-categories', [FormationCategoryController::class, 'store']);
    Route::match(['put', 'patch'], '/formation-categories/{formationCategory}', [FormationCategoryController::class, 'update'])
        ->whereNumber('formationCategory');
    Route::delete('/formation-categories/{formationCategory}', [FormationCategoryController::class, 'destroy'])
        ->whereNumber('formationCategory');

    Route::post('/formations/content-images', [FormationImageController::class, 'store']);
    Route::post('/formations', [FormationController::class, 'store']);
    Route::match(['put', 'patch'], '/formations/{formation}', [FormationController::class, 'update'])
        ->whereNumber('formation');
    Route::patch('/formations/{formation}/switch-status', [FormationController::class, 'switchStatus'])
        ->whereNumber('formation');
    Route::patch('/formations/{formation}/switch-state', [FormationController::class, 'switchState'])
        ->whereNumber('formation');
    Route::delete('/formations/{formation}', [FormationController::class, 'destroy'])->whereNumber('formation');

    Route::get('/participants', [ParticipantController::class, 'index']);
    Route::get('/participants/{participant}', [ParticipantController::class, 'show'])->whereNumber('participant');
    Route::match(['put', 'patch'], '/participants/{participant}', [ParticipantController::class, 'update'])
        ->whereNumber('participant');
    Route::delete('/participants/{participant}', [ParticipantController::class, 'destroy'])->whereNumber('participant');

    Route::get('/participations', [ParticipationController::class, 'index']);
    Route::get('/participations/{participation}', [ParticipationController::class, 'show'])->whereNumber('participation');
    Route::patch('/participations/{participation}/switch-status', [ParticipationController::class, 'switchStatus'])
        ->whereNumber('participation');
    Route::delete('/participations/{participation}', [ParticipationController::class, 'destroy'])
        ->whereNumber('participation');

    Route::get('/participations/{participation}/payments', [PaymentController::class, 'index'])
        ->whereNumber('participation');
    Route::post('/participations/{participation}/payments', [PaymentController::class, 'store'])
        ->whereNumber('participation');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->whereNumber('payment');
    Route::match(['put', 'patch'], '/payments/{payment}', [PaymentController::class, 'update'])
        ->whereNumber('payment');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->whereNumber('payment');
});
