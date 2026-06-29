<?php

namespace App\Modules\Jobs\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\DeveloperMoment;
use App\Modules\Jobs\Requests\StoreDeveloperMomentRequest;
use App\Modules\Jobs\Requests\UpdateDeveloperMomentRequest;
use App\Modules\Jobs\Resources\DeveloperMomentResource;
use App\Traits\ApiResponses;

class DeveloperMomentController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $developerMoments = DeveloperMoment::orderBy('created_at', 'desc')->get();

        return $this->successResponse(
            DeveloperMomentResource::collection($developerMoments),
            "Liste des developer moments chargée avec succès."
        );
    }

    public function store(StoreDeveloperMomentRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')
                ->store('developer-moments/photos', 'public');
        }

        $developerMoment = DeveloperMoment::create($data);

        logActivity(
            "Création d'un developer moment",
            $data,
            $developerMoment
        );

        return $this->successResponse(
            new DeveloperMomentResource($developerMoment),
            "Developer moment créé avec succès."
        );
    }

    public function show(string $id)
    {
        $developerMoment = DeveloperMoment::find($id);

        if (! $developerMoment) {
            return $this->errorResponse("Developer moment introuvable.");
        }

        return $this->successResponse(
            new DeveloperMomentResource($developerMoment),
            "Developer moment chargé avec succès."
        );
    }

    public function switchStatus(string $id)
    {
        $developerMoment = DeveloperMoment::find($id);

        if (! $developerMoment) {
            return $this->errorResponse("Developer moment introuvable.");
        }

        $developerMoment->is_active = ! $developerMoment->is_active;
        $developerMoment->save();

        logActivity(
            "Changement du statut d'un developer moment",
            $developerMoment->toArray(),
            $developerMoment
        );

        return $this->noContentSuccessResponse(
            "Statut du developer moment mis à jour avec succès."
        );
    }

    public function update(UpdateDeveloperMomentRequest $request, string $id)
    {
        $developerMoment = DeveloperMoment::find($id);

        if (! $developerMoment) {
            return $this->errorResponse("Developer moment introuvable.");
        }

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')
                ->store('developer-moments/photos', 'public');
        }

        $logData = [
            'old_value' => $developerMoment->toArray(),
            'new_value' => $data,
        ];

        $developerMoment->update($data);

        logActivity(
            "Modification d'un developer moment",
            $logData,
            $developerMoment
        );

        return $this->successResponse(
            new DeveloperMomentResource($developerMoment),
            "Developer moment modifié avec succès."
        );
    }

    public function destroy(string $id)
    {
        $developerMoment = DeveloperMoment::find($id);

        if (! $developerMoment) {
            return $this->errorResponse("Developer moment introuvable.");
        }

        logActivity(
            "Suppression d'un developer moment",
            $developerMoment->toArray(),
            $developerMoment
        );

        $developerMoment->delete();

        return $this->noContentSuccessResponse(
            "Developer moment supprimé avec succès."
        );
    }
}