<?php

namespace App\Modules\Jobs\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\Quote;
use App\Modules\Jobs\Requests\UpdateQuoteRequest;
use App\Modules\Jobs\Resources\QuoteResource;
use App\Traits\ApiResponses;

class QuoteController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $quotes = Quote::orderBy('created_at', 'desc')->get();

        return $this->successResponse(
            QuoteResource::collection($quotes),
            "Liste des demandes de devis chargée avec succès."
        );
    }

    public function show(string $id)
    {
        $quote = Quote::find($id);

        if (! $quote) {
            return $this->errorResponse("Demande de devis introuvable.");
        }

        return $this->successResponse(
            new QuoteResource($quote),
            "Demande de devis chargée avec succès."
        );
    }

    public function update(UpdateQuoteRequest $request, string $id)
    {
        $quote = Quote::find($id);

        if (! $quote) {
            return $this->errorResponse("Demande de devis introuvable.");
        }

        $data = $request->validated();

        $logData = [
            'old_value' => $quote->toArray(),
            'new_value' => $data,
        ];

        $quote->update($data);

        logActivity(
            "Modification d'une demande de devis",
            $logData,
            $quote
        );

        return $this->successResponse(
            new QuoteResource($quote),
            "Demande de devis modifiée avec succès."
        );
    }

    public function destroy(string $id)
    {
        $quote = Quote::find($id);

        if (! $quote) {
            return $this->errorResponse("Demande de devis introuvable.");
        }

        logActivity(
            "Suppression d'une demande de devis",
            $quote->toArray(),
            $quote
        );

        $quote->delete();

        return $this->noContentSuccessResponse(
            "Demande de devis supprimée avec succès."
        );
    }
}