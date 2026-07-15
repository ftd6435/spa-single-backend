<?php

use App\Modules\Sondage\Controllers\CompetitionController;
use App\Modules\Sondage\Controllers\CompetitionEquipeController;
use App\Modules\Sondage\Controllers\EquipeController;
use App\Modules\Sondage\Controllers\InitSondageController;
use App\Modules\Sondage\Controllers\RencontreController;
use App\Modules\Sondage\Controllers\VotantController;
use App\Modules\Sondage\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

// Routes PUBLIQUES — consultation des compétitions, équipes, sondages, rencontres
Route::prefix('v1')->group(function () {
    Route::get('/competitions', [CompetitionController::class, 'index']);
    Route::get('/competitions/{id}', [CompetitionController::class, 'show']);
    Route::get('/competitions/{competitionId}/equipes', [CompetitionEquipeController::class, 'show']);

    Route::get('/equipes', [EquipeController::class, 'index']);
    Route::get('/equipes/{id}', [EquipeController::class, 'show']);

    Route::get('/sondages', [InitSondageController::class, 'index']);
    Route::get('/sondages/{id}', [InitSondageController::class, 'show']);

    Route::get('/rencontres', [RencontreController::class, 'index']);
    Route::get('/rencontres/{id}', [RencontreController::class, 'show']);

    // Un votant s'enregistre puis soumet son pronostic pour un sondage
    Route::post('/votants', [VotantController::class, 'store']);
    Route::post('/votes', [VoteController::class, 'store']);
});

// Routes ADMIN — protégées par Sanctum (token requis)
Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::post('/competitions', [CompetitionController::class, 'store']);
    Route::put('/competitions/{id}', [CompetitionController::class, 'update']);
    Route::get('/competitions/{id}/switch-status', [CompetitionController::class, 'switchStatus']);
    Route::delete('/competitions/{id}', [CompetitionController::class, 'destroy']);

    Route::get('/competition-equipes', [CompetitionEquipeController::class, 'index']);
    Route::post('/competitions/{competitionId}/equipes', [CompetitionEquipeController::class, 'store']);
    Route::delete('/competitions/{competitionId}/equipes/{equipeId}', [CompetitionEquipeController::class, 'destroy']);

    Route::post('/equipes', [EquipeController::class, 'store']);
    Route::put('/equipes/{id}', [EquipeController::class, 'update']);
    Route::delete('/equipes/{id}', [EquipeController::class, 'destroy']);

    Route::post('/sondages', [InitSondageController::class, 'store']);
    Route::put('/sondages/{id}', [InitSondageController::class, 'update']);
    Route::get('/sondages/{id}/switch-status', [InitSondageController::class, 'switchStatus']);
    Route::delete('/sondages/{id}', [InitSondageController::class, 'destroy']);

    Route::post('/rencontres', [RencontreController::class, 'store']);
    Route::put('/rencontres/{id}', [RencontreController::class, 'update']);
    Route::delete('/rencontres/{id}', [RencontreController::class, 'destroy']);

    Route::get('/votants', [VotantController::class, 'index']);
    Route::get('/votants/{id}', [VotantController::class, 'show']);
    Route::put('/votants/{id}', [VotantController::class, 'update']);
    Route::delete('/votants/{id}', [VotantController::class, 'destroy']);

    Route::get('/votes', [VoteController::class, 'index']);
    Route::get('/votes/{id}', [VoteController::class, 'show']);
});
