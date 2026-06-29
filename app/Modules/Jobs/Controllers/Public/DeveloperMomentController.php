<?php

namespace App\Modules\Jobs\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\DeveloperMoment;
use App\Modules\Jobs\Resources\DeveloperMomentResource;
use App\Traits\ApiResponses;

class DeveloperMomentController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $developerMoments = DeveloperMoment::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            DeveloperMomentResource::collection($developerMoments),
            "Liste des developer moments chargée avec succès."
        );
    }

    public function show(string $id)
    {
        $developerMoment = DeveloperMoment::where('is_active', true)
            ->find($id);

        if (! $developerMoment) {
            return $this->errorResponse("Developer moment introuvable.");
        }

        return $this->successResponse(
            new DeveloperMomentResource($developerMoment),
            "Developer moment chargé avec succès."
        );
    }
}