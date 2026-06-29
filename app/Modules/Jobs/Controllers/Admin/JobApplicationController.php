<?php

namespace App\Modules\Jobs\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\JobApplication;
use App\Modules\Jobs\Requests\UpdateJobApplicationRequest;
use App\Modules\Jobs\Resources\JobApplicationResource;
use App\Traits\ApiResponses;

class JobApplicationController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $jobApplications = JobApplication::with('jobOpening', 'processes.processor')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            JobApplicationResource::collection($jobApplications),
            "Liste des candidatures chargée avec succès."
        );
    }

    public function show(string $id)
    {
        $jobApplication = JobApplication::with('jobOpening', 'processes.processor')->find($id);

        if (! $jobApplication) {
            return $this->errorResponse("Candidature introuvable.");
        }

        return $this->successResponse(
            new JobApplicationResource($jobApplication),
            "Candidature chargée avec succès."
        );
    }

    public function update(UpdateJobApplicationRequest $request, string $id)
    {
        $jobApplication = JobApplication::find($id);

        if (! $jobApplication) {
            return $this->errorResponse("Candidature introuvable.");
        }

        $data = $request->validated();

        if ($request->hasFile('cv_file')) {
            $data['cv_file'] = $request->file('cv_file')
                ->store('job-applications/cv', 'public');
        }

        $logData = [
            'old_value' => $jobApplication->toArray(),
            'new_value' => $data,
        ];

        $jobApplication->update($data);

        logActivity(
            "Modification d'une candidature",
            $logData,
            $jobApplication
        );

        return $this->successResponse(
            new JobApplicationResource(
                $jobApplication->load('jobOpening', 'processes.processor')
            ),
            "Candidature modifiée avec succès."
        );
    }

    public function destroy(string $id)
    {
        $jobApplication = JobApplication::find($id);

        if (! $jobApplication) {
            return $this->errorResponse("Candidature introuvable.");
        }

        logActivity(
            "Suppression d'une candidature",
            $jobApplication->toArray(),
            $jobApplication
        );

        $jobApplication->delete();

        return $this->noContentSuccessResponse(
            "Candidature supprimée avec succès."
        );
    }
}