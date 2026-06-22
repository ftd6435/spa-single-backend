<?php

namespace App\Modules\Offer\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Offer\Models\OfferType;
use App\Modules\Offer\Requests\OfferTypeRequest;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

// Gestion des types d'offres (CRUD)
class OfferTypeController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $offerTypes = OfferType::with('createdBy', 'updatedBy')->orderBy('created_at', 'desc')->get();

        return $this->successResponse($offerTypes, "Liste des types d'offre chargée avec succès.");
    }

    public function store(OfferTypeRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $offerType = OfferType::create($data);

        logActivity("Création d'un type d'offre", $data, $offerType);

        return $this->successResponse($offerType, "Type d'offre créé avec succès.");
    }

    public function show(string $id)
    {
        $offerType = OfferType::find($id);

        if (! $offerType) {
            return $this->errorResponse("Type d'offre introuvable");
        }

        return $this->successResponse($offerType, "Type d'offre demandé chargé avec succès");
    }

    public function update(OfferTypeRequest $request, string $id)
    {
        $offerType = OfferType::find($id);

        if (! $offerType) {
            return $this->errorResponse("Type d'offre introuvable");
        }

        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        // On garde l'ancienne valeur pour le log avant la mise à jour
        $logData = [
            'old_value' => $offerType->toArray(),
            'new_value' => $data,
        ];

        $offerType->update($data);

        logActivity("Modification d'un type d'offre", $logData, $offerType);

        return $this->successResponse($offerType, "Type d'offre modifié avec succès.");
    }

    public function destroy(string $id)
    {
        $offerType = OfferType::find($id);

        if (! $offerType) {
            return $this->errorResponse("Type d'offre introuvable");
        }

        logActivity("Suppression d'un type d'offre", $offerType->toArray(), $offerType);
        $offerType->delete();

        return $this->noContentSuccessResponse("Type d'offre supprimé avec succès");
    }
}
