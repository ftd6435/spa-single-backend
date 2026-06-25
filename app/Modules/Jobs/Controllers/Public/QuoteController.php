<?php

namespace App\Modules\Jobs\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\Quote;
use App\Modules\Jobs\Requests\StoreQuoteRequest;
use App\Modules\Jobs\Resources\QuoteResource;

class QuoteController extends Controller
{
    public function store(StoreQuoteRequest $request)
    {
        $quote = Quote::create($request->validated());

        return new QuoteResource($quote);
    }
}