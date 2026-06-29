<?php

namespace App\Modules\Jobs\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\JobApplicationProcess;
use App\Modules\Jobs\Requests\StoreJobApplicationProcessRequest;
use App\Modules\Jobs\Requests\UpdateJobApplicationProcessRequest;
use App\Modules\Jobs\Resources\JobApplicationProcessResource;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class JobApplicationProcessController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $processes = JobApplicationProcess::with('jobApplication', 'processor')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            JobApplicationProcessResource::collection($processes),
            "Liste des traitements de candidatures chargée avec succès."
        );
    }

    public function store(StoreJobApplicationProcessRequest $request)
    {
        $data = $request->validated();

        if (empty($data['processed_by'])) {
            $data['processed_by'] = Auth::id();
        }

        $process = JobApplicationProcess::create($data);

        logActivity(
            "Création d'un traitement de candidature",
            $data,
            $process
        );

        return $this->successResponse(
            new JobApplicationProcessResource(
                $process->load('jobApplication', 'processor')
            ),
            "Traitement de candidature créé avec succès."
        );
    }

    public function show(string $id)
    {
        $process = JobApplicationProcess::with('jobApplication', 'processor')->find($id);

        if (! $process) {
            return $this->errorResponse("Traitement de candidature introuvable.");
        }

        return $this->successResponse(
            new JobApplicationProcessResource($process),
            "Traitement de candidature chargé avec succès."
        );
    }

    public function update(UpdateJobApplicationProcessRequest $request, string $id)
    {
        $process = JobApplicationProcess::find($id);

        if (! $process) {
            return $this->errorResponse("Traitement de candidature introuvable.");
        }

        $data = $request->validated();

        if (empty($data['processed_by'])) {
            $data['processed_by'] = Auth::id();
        }

        $logData = [
            'old_value' => $process->toArray(),
            'new_value' => $data,
        ];

        $process->update($data);

        logActivity(
            "Modification d'un traitement de candidature",
            $logData,
            $process
        );

        return $this->successResponse(
            new JobApplicationProcessResource(
                $process->load('jobApplication', 'processor')
            ),
            "Traitement de candidature modifié avec succès."
        );
    }

    public function destroy(string $id)
    {
        $process = JobApplicationProcess::find($id);

        if (! $process) {
            return $this->errorResponse("Traitement de candidature introuvable.");
        }

        logActivity(
            "Suppression d'un traitement de candidature",
            $process->toArray(),
            $process
        );

        $process->delete();

        return $this->noContentSuccessResponse(
            "Traitement de candidature supprimé avec succès."
        );
    }
}