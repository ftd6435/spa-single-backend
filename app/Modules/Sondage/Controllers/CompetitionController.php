<?php

namespace App\Modules\Sondage\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sondage\Models\Competition;
use App\Modules\Sondage\Requests\StoreCompetitionRequest;
use App\Modules\Sondage\Requests\UpdateCompetitionRequest;
use App\Modules\Sondage\Resources\CompetitionResource;
use App\Traits\ApiResponses;

class CompetitionController extends Controller
{
    use ApiResponses;

    // Route publique — les visiteurs ne voient que les compétitions actives
    public function index()
    {
        $query = Competition::query();

        if (! auth('sanctum')->check()) {
            $query->where('is_active', true);
        }

        return $this->successResponse(
            CompetitionResource::collection($query->orderBy('created_at', 'desc')->get()),
            "Liste des compétitions chargée avec succès."
        );
    }

    // Route admin — création d'une nouvelle compétition
    public function store(StoreCompetitionRequest $request)
    {
        $data = $request->validated();

        $competition = Competition::create($data);

        logActivity("Création d'une compétition", $data, $competition);

        return $this->successResponse(new CompetitionResource($competition), "Compétition créée avec succès.");
    }

    // Route publique — détail d'une compétition
    public function show(string $id)
    {
        $query = Competition::query();

        if (! auth('sanctum')->check()) {
            $query->where('is_active', true);
        }

        $competition = $query->find($id);

        if (! $competition) {
            return $this->errorResponse("Compétition introuvable.");
        }

        return $this->successResponse(new CompetitionResource($competition), "Compétition chargée avec succès.");
    }

    // Route admin — modification d'une compétition existante
    public function update(UpdateCompetitionRequest $request, string $id)
    {
        $competition = Competition::find($id);

        if (! $competition) {
            return $this->errorResponse("Compétition introuvable.");
        }

        $data = $request->validated();

        $logData = [
            'old_value' => $competition->toArray(),
            'new_value' => $data,
        ];

        $competition->update($data);

        logActivity("Modification d'une compétition", $logData, $competition);

        return $this->successResponse(new CompetitionResource($competition), "Compétition modifiée avec succès.");
    }

    // Route admin — activer/désactiver une compétition
    public function switchStatus(string $id)
    {
        $competition = Competition::find($id);

        if (! $competition) {
            return $this->errorResponse("Compétition introuvable.");
        }

        $competition->is_active = ! $competition->is_active;
        $competition->save();

        logActivity("Changement du statut d'une compétition", $competition->toArray(), $competition);

        return $this->noContentSuccessResponse("Statut de la compétition mis à jour avec succès.");
    }

    // Route admin — suppression définitive d'une compétition
    public function destroy(string $id)
    {
        $competition = Competition::find($id);

        if (! $competition) {
            return $this->errorResponse("Compétition introuvable.");
        }

        logActivity("Suppression d'une compétition", $competition->toArray(), $competition);
        $competition->delete();

        return $this->noContentSuccessResponse("Compétition supprimée avec succès.");
    }
}
