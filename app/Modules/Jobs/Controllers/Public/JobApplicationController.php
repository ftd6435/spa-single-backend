<?php

namespace App\Modules\Jobs\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\JobApplication;
use App\Modules\Jobs\Requests\StoreJobApplicationRequest;
use App\Modules\Jobs\Resources\JobApplicationResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;

class JobApplicationController extends Controller
{
    use ApiResponses, CloudflareUpload;

    public function store(StoreJobApplicationRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('cv_file')) {
            $data['cv_file'] = $this->uploadFile($request->file('cv_file'), 'candidatures');
        }

        $application = JobApplication::create($data);

        logActivity(
            "Soumission d'une candidature",
            $data,
            $application
        );

        return $this->successResponse(
            new JobApplicationResource($application),
            "Candidature envoyée avec succès."
        );
    }
}
