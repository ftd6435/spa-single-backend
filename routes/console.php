<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Nettoyage quotidien des images de contenu d'articles jamais rattachées (rédaction abandonnée)
Schedule::command('articles:clean-orphan-images')->daily();
Schedule::command('formations:clean-orphan-images')->daily();
Schedule::command('geoip:update')->weekly();
// ...
