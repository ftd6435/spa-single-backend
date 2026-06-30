<?php

use Illuminate\Support\Facades\Route;

use App\Modules\Jobs\Controllers\Public\JobApplicationController as PublicJobApplicationController;
use App\Modules\Jobs\Controllers\Public\NewsletterController as PublicNewsletterController;
use App\Modules\Jobs\Controllers\Public\QuoteController as PublicQuoteController;

use App\Modules\Jobs\Controllers\Admin\DeveloperMomentController as AdminDeveloperMomentController;
use App\Modules\Jobs\Controllers\Admin\PageController as AdminPageController;
use App\Modules\Jobs\Controllers\Admin\HeroController as AdminHeroController;
use App\Modules\Jobs\Controllers\Admin\JobOpeningController as AdminJobOpeningController;
use App\Modules\Jobs\Controllers\Admin\JobApplicationController as AdminJobApplicationController;
use App\Modules\Jobs\Controllers\Admin\JobApplicationProcessController as AdminJobApplicationProcessController;
use App\Modules\Jobs\Controllers\Admin\NewsletterController as AdminNewsletterController;
use App\Modules\Jobs\Controllers\Admin\QuoteController as AdminQuoteController;

// Public Routes
Route::prefix('jobs')->group(function () {
    Route::get('/developer-moments', [AdminDeveloperMomentController::class, 'index']);
    Route::get('/developer-moments/{developerMoment}', [AdminDeveloperMomentController::class, 'show']);

    Route::get('/pages', [AdminPageController::class, 'index']);
    Route::get('/pages/{page}', [AdminPageController::class, 'show']);

    Route::get('/heroes', [AdminHeroController::class, 'index']);
    Route::get('/heroes/{hero}', [AdminHeroController::class, 'show']);

    Route::get('/openings', [AdminJobOpeningController::class, 'index']);
    Route::get('/openings/{jobOpening}', [AdminJobOpeningController::class, 'show']);

    Route::post('/applications', [PublicJobApplicationController::class, 'store']);
    Route::post('/newsletters', [PublicNewsletterController::class, 'store']);
    Route::post('/quotes', [PublicQuoteController::class, 'store']);
});


// Admin Routes

Route::prefix('admin/jobs')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::apiResource('developer-moments', AdminDeveloperMomentController::class);
        Route::patch('developer-moments/{developerMoment}/switch-status', [AdminDeveloperMomentController::class, 'switchStatus']);

        Route::apiResource('pages', AdminPageController::class);
        Route::patch('pages/{page}/switch-status', [AdminPageController::class, 'switchStatus']);

        Route::apiResource('heroes', AdminHeroController::class);
        Route::patch('heroes/{hero}/switch-status', [AdminHeroController::class, 'switchStatus']);

        Route::apiResource('openings', AdminJobOpeningController::class);
        Route::patch('openings/{opening}/switch-status', [AdminJobOpeningController::class, 'switchStatus']);

        Route::apiResource('applications', AdminJobApplicationController::class)->except(['store']);
        Route::apiResource('application-processes', AdminJobApplicationProcessController::class);

        Route::apiResource('newsletters', AdminNewsletterController::class)->only(['index', 'show', 'destroy']);
        Route::patch('newsletters/{newsletter}/switch-status', [AdminNewsletterController::class, 'switchStatus']);

        Route::apiResource('quotes', AdminQuoteController::class)->except(['store']);
    });
