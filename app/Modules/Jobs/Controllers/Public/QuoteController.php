<?php

namespace App\Modules\Jobs\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\Quote;
use App\Modules\Jobs\Requests\StoreQuoteRequest;
use App\Modules\Jobs\Resources\QuoteResource;
use App\Traits\ApiResponses;

class QuoteController extends Controller
{
    use ApiResponses;

    public function store(StoreQuoteRequest $request)
    {
        $data = $request->validated();

        $quote = Quote::create($data);

        logActivity(
            "Soumission d'une demande de devis",
            $data,
            $quote
        );

        return $this->successResponse(
            new QuoteResource($quote),
            "Demande de devis envoyée avec succès."
        );
    }
}