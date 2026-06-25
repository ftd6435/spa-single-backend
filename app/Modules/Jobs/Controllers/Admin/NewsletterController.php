<?php

namespace App\Modules\Jobs\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\Newsletter;
use App\Modules\Jobs\Resources\NewsletterResource;

class NewsletterController extends Controller
{
    public function index()
    {
        return NewsletterResource::collection(
            Newsletter::latest()->paginate(10)
        );
    }

    public function show(Newsletter $newsletter)
    {
        return new NewsletterResource($newsletter);
    }

    public function update(Newsletter $newsletter)
    {
        $newsletter->update([
            'is_subscribed' => ! $newsletter->is_subscribed,
        ]);

        return new NewsletterResource($newsletter);
    }

    public function destroy(Newsletter $newsletter)
    {
        $newsletter->delete();

        return response()->json([
            'message' => 'Newsletter deleted successfully.'
        ]);
    }
}