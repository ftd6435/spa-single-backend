<?php

namespace App\Modules\Jobs\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\JobOpening;
use App\Modules\Jobs\Resources\JobOpeningResource;
use App\Traits\ApiResponses;

class JobOpeningController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $jobOpenings = JobOpening::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            JobOpeningResource::collection($jobOpenings),
            "Liste des offres d'emploi chargée avec succès."
        );
    }

    public function show(string $id)
    {
        $jobOpening = JobOpening::where('is_active', true)
            ->find($id);

        if (! $jobOpening) {
            return $this->errorResponse("Offre d'emploi introuvable.");
        }

        return $this->successResponse(
            new JobOpeningResource($jobOpening),
            "Offre d'emploi chargée avec succès."
        );
    }
}