<?php

namespace App\Modules\Jobs\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\Hero;
use App\Modules\Jobs\Resources\HeroResource;
use App\Traits\ApiResponses;

class HeroController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $heroes = Hero::with('page')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            HeroResource::collection($heroes),
            "Liste des heroes chargée avec succès."
        );
    }

    public function show(string $id)
    {
        $hero = Hero::with('page')
            ->where('is_active', true)
            ->find($id);

        if (! $hero) {
            return $this->errorResponse("Hero introuvable.");
        }

        return $this->successResponse(
            new HeroResource($hero),
            "Hero chargé avec succès."
        );
    }
}