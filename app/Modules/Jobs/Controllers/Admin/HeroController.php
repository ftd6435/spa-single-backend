<?php

namespace App\Modules\Jobs\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\Hero;
use App\Modules\Jobs\Requests\StoreHeroRequest;
use App\Modules\Jobs\Requests\UpdateHeroRequest;
use App\Modules\Jobs\Resources\HeroResource;
use App\Traits\ApiResponses;

class HeroController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $heroes = Hero::with('page')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            HeroResource::collection($heroes),
            "Liste des heroes chargée avec succès."
        );
    }

    public function store(StoreHeroRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')
                ->store('heroes/files', 'public');
        }

        $hero = Hero::create($data);

        logActivity(
            "Création d'un hero",
            $data,
            $hero
        );

        return $this->successResponse(
            new HeroResource($hero->load('page')),
            "Hero créé avec succès."
        );
    }

    public function show(string $id)
    {
        $hero = Hero::with('page')->find($id);

        if (! $hero) {
            return $this->errorResponse("Hero introuvable.");
        }

        return $this->successResponse(
            new HeroResource($hero),
            "Hero chargé avec succès."
        );
    }

    public function switchStatus(string $id)
    {
        $hero = Hero::find($id);

        if (! $hero) {
            return $this->errorResponse("Hero introuvable.");
        }

        $oldValue = $hero->toArray();

        $hero->is_active = ! $hero->is_active;
        $hero->save();

        $logData = [
            'old_value' => $oldValue,
            'new_value' => $hero->toArray(),
        ];

        logActivity(
            "Changement du statut d'un hero",
            $logData,
            $hero
        );

        return $this->noContentSuccessResponse(
            "Statut du hero mis à jour avec succès."
        );
    }

    public function update(UpdateHeroRequest $request, string $id)
    {
        $hero = Hero::find($id);

        if (! $hero) {
            return $this->errorResponse("Hero introuvable.");
        }

        $data = $request->validated();

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')
                ->store('heroes/files', 'public');
        }

        $logData = [
            'old_value' => $hero->toArray(),
            'new_value' => $data,
        ];

        $hero->update($data);

        logActivity(
            "Modification d'un hero",
            $logData,
            $hero
        );

        return $this->successResponse(
            new HeroResource($hero->load('page')),
            "Hero modifié avec succès."
        );
    }

    public function destroy(string $id)
    {
        $hero = Hero::find($id);

        if (! $hero) {
            return $this->errorResponse("Hero introuvable.");
        }

        logActivity(
            "Suppression d'un hero",
            $hero->toArray(),
            $hero
        );

        $hero->delete();

        return $this->noContentSuccessResponse(
            "Hero supprimé avec succès."
        );
    }
}