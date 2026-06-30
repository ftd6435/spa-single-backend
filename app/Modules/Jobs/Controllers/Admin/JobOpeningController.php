<?php

namespace App\Modules\Jobs\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\JobOpening;
use App\Modules\Jobs\Requests\StoreJobOpeningRequest;
use App\Modules\Jobs\Requests\UpdateJobOpeningRequest;
use App\Modules\Jobs\Resources\JobOpeningResource;
use App\Traits\ApiResponses;

class JobOpeningController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $query = JobOpening::with('applications');

        if (! auth('sanctum')->check()) {
            $query->where('is_active', true);
        }

        return $this->successResponse(
            JobOpeningResource::collection($query->orderBy('created_at', 'desc')->get()),
            "Liste des offres d'emploi chargée avec succès."
        );
    }

    public function store(StoreJobOpeningRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('job-openings/images', 'public');
        }

        $jobOpening = JobOpening::create($data);

        logActivity(
            "Création d'une offre d'emploi",
            $data,
            $jobOpening
        );

        return $this->successResponse(
            new JobOpeningResource($jobOpening),
            "Offre d'emploi créée avec succès."
        );
    }

    public function show(string $id)
    {
        $query = JobOpening::with('applications');

        if (! auth('sanctum')->check()) {
            $query->where('is_active', true);
        }

        $jobOpening = $query->find($id);

        if (! $jobOpening) {
            return $this->errorResponse("Offre d'emploi introuvable.");
        }

        return $this->successResponse(
            new JobOpeningResource($jobOpening),
            "Offre d'emploi chargée avec succès."
        );
    }

    public function switchStatus(string $id)
    {
        $jobOpening = JobOpening::find($id);

        if (! $jobOpening) {
            return $this->errorResponse("Offre d'emploi introuvable.");
        }

        $oldValue = $jobOpening->toArray();

        $jobOpening->is_active = ! $jobOpening->is_active;
        $jobOpening->save();

        $logData = [
            'old_value' => $oldValue,
            'new_value' => $jobOpening->toArray(),
        ];

        logActivity(
            "Changement du statut d'une offre d'emploi",
            $logData,
            $jobOpening
        );

        return $this->noContentSuccessResponse(
            "Statut de l'offre d'emploi mis à jour avec succès."
        );
    }

    public function update(UpdateJobOpeningRequest $request, string $id)
    {
        $jobOpening = JobOpening::find($id);

        if (! $jobOpening) {
            return $this->errorResponse("Offre d'emploi introuvable.");
        }

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('job-openings/images', 'public');
        }

        $logData = [
            'old_value' => $jobOpening->toArray(),
            'new_value' => $data,
        ];

        $jobOpening->update($data);

        logActivity(
            "Modification d'une offre d'emploi",
            $logData,
            $jobOpening
        );

        return $this->successResponse(
            new JobOpeningResource($jobOpening->load('applications')),
            "Offre d'emploi modifiée avec succès."
        );
    }

    public function destroy(string $id)
    {
        $jobOpening = JobOpening::find($id);

        if (! $jobOpening) {
            return $this->errorResponse("Offre d'emploi introuvable.");
        }

        logActivity(
            "Suppression d'une offre d'emploi",
            $jobOpening->toArray(),
            $jobOpening
        );

        $jobOpening->delete();

        return $this->noContentSuccessResponse(
            "Offre d'emploi supprimée avec succès."
        );
    }
}