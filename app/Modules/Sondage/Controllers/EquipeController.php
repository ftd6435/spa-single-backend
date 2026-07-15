<?php

namespace App\Modules\Sondage\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sondage\Models\Equipes;
use App\Modules\Sondage\Requests\StoreEquipeRequest;
use App\Modules\Sondage\Requests\UpdateEquipeRequest;
use App\Modules\Sondage\Resources\EquipeResource;
use App\Traits\ApiResponses;

class EquipeController extends Controller
{
    use ApiResponses;

    // Route publique — liste des équipes
    public function index()
    {
        $equipes = Equipes::orderBy('libelle')->get();

        return $this->successResponse(EquipeResource::collection($equipes), "Liste des équipes chargée avec succès.");
    }

    // Route admin — création d'une nouvelle équipe
    public function store(StoreEquipeRequest $request)
    {
        $data = $request->validated();

        $equipe = Equipes::create($data);

        logActivity("Création d'une équipe", $data, $equipe);

        return $this->successResponse(new EquipeResource($equipe), "Équipe créée avec succès.");
    }

    // Route publique — détail d'une équipe
    public function show(string $id)
    {
        $equipe = Equipes::find($id);

        if (! $equipe) {
            return $this->errorResponse("Équipe introuvable.");
        }

        return $this->successResponse(new EquipeResource($equipe), "Équipe chargée avec succès.");
    }

    // Route admin — modification d'une équipe existante
    public function update(UpdateEquipeRequest $request, string $id)
    {
        $equipe = Equipes::find($id);

        if (! $equipe) {
            return $this->errorResponse("Équipe introuvable.");
        }

        $data = $request->validated();

        $logData = [
            'old_value' => $equipe->toArray(),
            'new_value' => $data,
        ];

        $equipe->update($data);

        logActivity("Modification d'une équipe", $logData, $equipe);

        return $this->successResponse(new EquipeResource($equipe), "Équipe modifiée avec succès.");
    }

    // Route admin — suppression définitive d'une équipe
    public function destroy(string $id)
    {
        $equipe = Equipes::find($id);

        if (! $equipe) {
            return $this->errorResponse("Équipe introuvable.");
        }

        logActivity("Suppression d'une équipe", $equipe->toArray(), $equipe);
        $equipe->delete();

        return $this->noContentSuccessResponse("Équipe supprimée avec succès.");
    }
}
