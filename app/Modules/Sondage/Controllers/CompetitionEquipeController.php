<?php

namespace App\Modules\Sondage\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sondage\Models\Competition;
use App\Modules\Sondage\Models\CompetitionEquipe;
use App\Modules\Sondage\Requests\StoreCompetitionEquipeRequest;
use App\Modules\Sondage\Resources\CompetitionEquipeResource;
use App\Modules\Sondage\Resources\EquipeResource;
use App\Traits\ApiResponses;

class CompetitionEquipeController extends Controller
{
    use ApiResponses;

    // Route admin — liste de tous les engagements équipe/compétition
    public function index()
    {
        $competitionEquipes = CompetitionEquipe::with(['competition', 'equipe'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            CompetitionEquipeResource::collection($competitionEquipes),
            "Liste des engagements chargée avec succès."
        );
    }

    // Route publique — liste des équipes engagées dans une compétition
    public function show(string $competitionId)
    {
        $competition = Competition::find($competitionId);

        if (! $competition) {
            return $this->errorResponse("Compétition introuvable.");
        }

        return $this->successResponse(
            EquipeResource::collection($competition->equipes),
            "Liste des équipes de la compétition chargée avec succès."
        );
    }

    // Route admin — engager une équipe dans une compétition
    public function store(StoreCompetitionEquipeRequest $request, string $competitionId)
    {
        $competition = Competition::find($competitionId);

        if (! $competition) {
            return $this->errorResponse("Compétition introuvable.");
        }

        $competition->equipes()->attach($request->validated('equipe_id'));

        logActivity(
            "Ajout d'une équipe à une compétition",
            ['competition_id' => $competition->id, 'equipe_id' => $request->validated('equipe_id')],
            $competition
        );

        return $this->successResponse(
            EquipeResource::collection($competition->equipes()->get()),
            "Équipe ajoutée à la compétition avec succès."
        );
    }

    // Route admin — retirer une équipe d'une compétition
    public function destroy(string $competitionId, string $equipeId)
    {
        $competition = Competition::find($competitionId);

        if (! $competition) {
            return $this->errorResponse("Compétition introuvable.");
        }

        $competition->equipes()->detach($equipeId);

        logActivity(
            "Retrait d'une équipe d'une compétition",
            ['competition_id' => $competition->id, 'equipe_id' => $equipeId],
            $competition
        );

        return $this->noContentSuccessResponse("Équipe retirée de la compétition avec succès.");
    }
}
