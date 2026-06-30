<?php

use Illuminate\Support\Facades\Route;

use App\Modules\Jobs\Controllers\Public\DeveloperMomentController as PublicDeveloperMomentController;
use App\Modules\Jobs\Controllers\Public\PageController as PublicPageController;
use App\Modules\Jobs\Controllers\Public\HeroController as PublicHeroController;
use App\Modules\Jobs\Controllers\Public\JobOpeningController as PublicJobOpeningController;
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


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::prefix('jobs')->group(function () {
    Route::get('/developer-moments', [PublicDeveloperMomentController::class, 'index']);
    Route::get('/developer-moments/{developerMoment}', [PublicDeveloperMomentController::class, 'show']);

    Route::get('/pages', [PublicPageController::class, 'index']);
    Route::get('/pages/{page}', [PublicPageController::class, 'show']);

    Route::get('/heroes', [PublicHeroController::class, 'index']);
    Route::get('/heroes/{hero}', [PublicHeroController::class, 'show']);

    Route::get('/openings', [PublicJobOpeningController::class, 'index']);
    Route::get('/openings/{jobOpening}', [PublicJobOpeningController::class, 'show']);

    Route::post('/applications', [PublicJobApplicationController::class, 'store']);
    Route::post('/newsletters', [PublicNewsletterController::class, 'store']);
    Route::post('/quotes', [PublicQuoteController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin/jobs')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::apiResource('developer-moments', AdminDeveloperMomentController::class);
        Route::apiResource('pages', AdminPageController::class);
        Route::apiResource('heroes', AdminHeroController::class);
        Route::apiResource('openings', AdminJobOpeningController::class);
        Route::apiResource('applications', AdminJobApplicationController::class)->except(['store']);
        Route::apiResource('application-processes', AdminJobApplicationProcessController::class);
        Route::apiResource('newsletters', AdminNewsletterController::class)->except(['store']);
        Route::apiResource('quotes', AdminQuoteController::class)->except(['store']);
    });
