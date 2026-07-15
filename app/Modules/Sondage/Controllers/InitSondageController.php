<?php

namespace App\Modules\Sondage\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sondage\Models\InitSondage;
use App\Modules\Sondage\Requests\StoreInitSondageRequest;
use App\Modules\Sondage\Requests\UpdateInitSondageRequest;
use App\Modules\Sondage\Resources\InitSondageResource;
use App\Traits\ApiResponses;

class InitSondageController extends Controller
{
    use ApiResponses;

    // Route publique — les visiteurs ne voient que les sondages actifs
    public function index()
    {
        $query = InitSondage::with('competition');

        if (! auth('sanctum')->check()) {
            $query->where('is_active', true);
        }

        return $this->successResponse(
            InitSondageResource::collection($query->orderBy('created_at', 'desc')->get()),
            "Liste des sondages chargée avec succès."
        );
    }

    // Route admin — création d'un nouveau sondage
    public function store(StoreInitSondageRequest $request)
    {
        $data = $request->validated();

        $initSondage = InitSondage::create($data);

        logActivity("Création d'un sondage", $data, $initSondage);

        return $this->successResponse(
            new InitSondageResource($initSondage->load('competition')),
            "Sondage créé avec succès."
        );
    }

    // Route publique — détail d'un sondage
    public function show(string $id)
    {
        $query = InitSondage::with('competition');

        if (! auth('sanctum')->check()) {
            $query->where('is_active', true);
        }

        $initSondage = $query->find($id);

        if (! $initSondage) {
            return $this->errorResponse("Sondage introuvable.");
        }

        return $this->successResponse(new InitSondageResource($initSondage), "Sondage chargé avec succès.");
    }

    // Route admin — modification d'un sondage existant
    public function update(UpdateInitSondageRequest $request, string $id)
    {
        $initSondage = InitSondage::find($id);

        if (! $initSondage) {
            return $this->errorResponse("Sondage introuvable.");
        }

        $data = $request->validated();

        $logData = [
            'old_value' => $initSondage->toArray(),
            'new_value' => $data,
        ];

        $initSondage->update($data);

        logActivity("Modification d'un sondage", $logData, $initSondage);

        return $this->successResponse(
            new InitSondageResource($initSondage->load('competition')),
            "Sondage modifié avec succès."
        );
    }

    // Route admin — activer/désactiver un sondage
    public function switchStatus(string $id)
    {
        $initSondage = InitSondage::find($id);

        if (! $initSondage) {
            return $this->errorResponse("Sondage introuvable.");
        }

        $initSondage->is_active = ! $initSondage->is_active;
        $initSondage->save();

        logActivity("Changement du statut d'un sondage", $initSondage->toArray(), $initSondage);

        return $this->noContentSuccessResponse("Statut du sondage mis à jour avec succès.");
    }

    // Route admin — suppression définitive d'un sondage
    public function destroy(string $id)
    {
        $initSondage = InitSondage::find($id);

        if (! $initSondage) {
            return $this->errorResponse("Sondage introuvable.");
        }

        logActivity("Suppression d'un sondage", $initSondage->toArray(), $initSondage);
        $initSondage->delete();

        return $this->noContentSuccessResponse("Sondage supprimé avec succès.");
    }
}
