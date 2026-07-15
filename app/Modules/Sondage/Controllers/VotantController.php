<?php

namespace App\Modules\Sondage\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sondage\Models\Votant;
use App\Modules\Sondage\Requests\StoreVotantRequest;
use App\Modules\Sondage\Requests\UpdateVotantRequest;
use App\Modules\Sondage\Resources\VotantResource;
use App\Traits\ApiResponses;

class VotantController extends Controller
{
    use ApiResponses;

    // Route admin — liste des votants
    public function index()
    {
        return $this->successResponse(
            VotantResource::collection(Votant::orderBy('created_at', 'desc')->get()),
            "Liste des votants chargée avec succès."
        );
    }

    // Route publique — inscription d'un votant avant de participer à un sondage
    public function store(StoreVotantRequest $request)
    {
        $data = $request->validated();

        $votant = Votant::create($data);

        logActivity("Création d'un votant", $data, $votant);

        return $this->successResponse(new VotantResource($votant), "Votant créé avec succès.");
    }

    // Route admin — détail d'un votant
    public function show(string $id)
    {
        $votant = Votant::find($id);

        if (! $votant) {
            return $this->errorResponse("Votant introuvable.");
        }

        return $this->successResponse(new VotantResource($votant), "Votant chargé avec succès.");
    }

    // Route admin — modification d'un votant existant
    public function update(UpdateVotantRequest $request, string $id)
    {
        $votant = Votant::find($id);

        if (! $votant) {
            return $this->errorResponse("Votant introuvable.");
        }

        $data = $request->validated();

        $logData = [
            'old_value' => $votant->toArray(),
            'new_value' => $data,
        ];

        $votant->update($data);

        logActivity("Modification d'un votant", $logData, $votant);

        return $this->successResponse(new VotantResource($votant), "Votant modifié avec succès.");
    }

    // Route admin — suppression définitive d'un votant
    public function destroy(string $id)
    {
        $votant = Votant::find($id);

        if (! $votant) {
            return $this->errorResponse("Votant introuvable.");
        }

        logActivity("Suppression d'un votant", $votant->toArray(), $votant);
        $votant->delete();

        return $this->noContentSuccessResponse("Votant supprimé avec succès.");
    }
}
