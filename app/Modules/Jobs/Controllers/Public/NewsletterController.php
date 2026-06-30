<?php

namespace App\Modules\Jobs\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\Newsletter;
use App\Modules\Jobs\Requests\StoreNewsletterRequest;
use App\Modules\Jobs\Resources\NewsletterResource;
use App\Traits\ApiResponses;

class NewsletterController extends Controller
{
    use ApiResponses;

    public function store(StoreNewsletterRequest $request)
    {
        $data = $request->validated();

        $newsletter = Newsletter::create($data);

        logActivity(
            "Soumission d'un abonnement newsletter",
            $data,
            $newsletter
        );

        return $this->successResponse(
            new NewsletterResource($newsletter),
            "Abonnement newsletter enregistré avec succès."
        );
    }
}
