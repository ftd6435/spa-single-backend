<?php

namespace App\Modules\Jobs\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\Quote;
use App\Modules\Jobs\Requests\UpdateQuoteRequest;
use App\Modules\Jobs\Resources\QuoteResource;

class QuoteController extends Controller
{
    public function index()
    {
        return QuoteResource::collection(
            Quote::latest()->paginate(10)
        );
    }

    public function show(Quote $quote)
    {
        return new QuoteResource($quote);
    }

    public function update(UpdateQuoteRequest $request, Quote $quote)
    {
        $quote->update($request->validated());

        return new QuoteResource($quote);
    }

    public function destroy(Quote $quote)
    {
        $quote->delete();

        return response()->json([
            'message' => 'Quote deleted successfully.'
        ]);
    }
}