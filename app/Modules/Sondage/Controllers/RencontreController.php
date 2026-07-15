<?php

namespace App\Modules\Sondage\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sondage\Models\Rencontre;
use App\Modules\Sondage\Requests\StoreRencontreRequest;
use App\Modules\Sondage\Requests\UpdateRencontreRequest;
use App\Modules\Sondage\Resources\RencontreResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class RencontreController extends Controller
{
    use ApiResponses;

    // Route publique — liste des rencontres, filtrable par sondage (?init_sondage_id=)
    public function index(Request $request)
    {
        $query = Rencontre::with(['homeTeam', 'awayTeam', 'winner']);

        if ($request->filled('init_sondage_id')) {
            $query->where('init_sondage_id', $request->input('init_sondage_id'));
        }

        return $this->successResponse(
            RencontreResource::collection($query->orderBy('date_rencontre')->get()),
            "Liste des rencontres chargée avec succès."
        );
    }

    // Route admin — création d'une nouvelle rencontre
    public function store(StoreRencontreRequest $request)
    {
        $data = $request->validated();

        $rencontre = Rencontre::create($data);

        logActivity("Création d'une rencontre", $data, $rencontre);

        return $this->successResponse(
            new RencontreResource($rencontre->load(['homeTeam', 'awayTeam', 'winner'])),
            "Rencontre créée avec succès."
        );
    }

    // Route publique — détail d'une rencontre
    public function show(string $id)
    {
        $rencontre = Rencontre::with(['homeTeam', 'awayTeam', 'winner'])->find($id);

        if (! $rencontre) {
            return $this->errorResponse("Rencontre introuvable.");
        }

        return $this->successResponse(new RencontreResource($rencontre), "Rencontre chargée avec succès.");
    }

    // Route admin — modification d'une rencontre existante (score, vainqueur, etc.)
    public function update(UpdateRencontreRequest $request, string $id)
    {
        $rencontre = Rencontre::find($id);

        if (! $rencontre) {
            return $this->errorResponse("Rencontre introuvable.");
        }

        $data = $request->validated();

        $logData = [
            'old_value' => $rencontre->toArray(),
            'new_value' => $data,
        ];

        $rencontre->update($data);

        logActivity("Modification d'une rencontre", $logData, $rencontre);

        return $this->successResponse(
            new RencontreResource($rencontre->load(['homeTeam', 'awayTeam', 'winner'])),
            "Rencontre modifiée avec succès."
        );
    }

    // Route admin — suppression définitive d'une rencontre
    public function destroy(string $id)
    {
        $rencontre = Rencontre::find($id);

        if (! $rencontre) {
            return $this->errorResponse("Rencontre introuvable.");
        }

        logActivity("Suppression d'une rencontre", $rencontre->toArray(), $rencontre);
        $rencontre->delete();

        return $this->noContentSuccessResponse("Rencontre supprimée avec succès.");
    }
}
