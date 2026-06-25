<?php

namespace App\Modules\Jobs\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\Hero;
use App\Modules\Jobs\Resources\HeroResource;

class HeroController extends Controller
{
    public function index()
    {
        return HeroResource::collection(
            Hero::with('page')
                ->where('is_active', true)
                ->latest()
                ->get()
        );
    }

    public function show(Hero $hero)
    {
        return new HeroResource($hero->load('page'));
    }
}