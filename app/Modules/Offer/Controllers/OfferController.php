<?php

namespace App\Modules\Offer\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Offer\Models\Offer;
use App\Modules\Offer\Requests\OfferRequest;
use App\Modules\Offer\Resources\OfferResource;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class OfferController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $offers = Offer::with('offerType')->orderBy('created_at', 'desc')->get();

        return $this->successResponse(OfferResource::collection($offers), "Liste des offres chargée avec succès.");
    }

    public function store(OfferRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $offer = Offer::create($data);

        logActivity("Création d'une offre", $data, $offer);

        return $this->successResponse(new OfferResource($offer->load('offerType')), "Offre créée avec succès.");
    }

    public function show(string $id)
    {
        $offer = Offer::with('offerType')->find($id);

        if (! $offer) {
            return $this->errorResponse("Offre introuvable");
        }

        return $this->successResponse(new OfferResource($offer), "Offre chargée avec succès.");
    }

    public function update(OfferRequest $request, string $id)
    {
        $offer = Offer::find($id);

        if (! $offer) {
            return $this->errorResponse("Offre introuvable");
        }

        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        $logData = [
            'old_value' => $offer->toArray(),
            'new_value' => $data,
        ];

        $offer->update($data);

        logActivity("Modification d'une offre", $logData, $offer);

        return $this->successResponse(new OfferResource($offer->load('offerType')), "Offre modifiée avec succès.");
    }

    public function destroy(string $id)
    {
        $offer = Offer::find($id);

        if (! $offer) {
            return $this->errorResponse("Offre introuvable");
        }

        logActivity("Suppression d'une offre", $offer->toArray(), $offer);
        $offer->delete();

        return $this->noContentSuccessResponse("Offre supprimée avec succès");
    }
}
