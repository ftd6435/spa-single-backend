<?php

namespace App\Modules\Jobs\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\DeveloperMoment;
use App\Modules\Jobs\Requests\StoreDeveloperMomentRequest;
use App\Modules\Jobs\Requests\UpdateDeveloperMomentRequest;
use App\Modules\Jobs\Resources\DeveloperMomentResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;

class DeveloperMomentController extends Controller
{
    use ApiResponses, CloudflareUpload;

    public function index()
    {
        $query = DeveloperMoment::query();

        if (! auth('sanctum')->check()) {
            $query->where('is_active', true);
        }

        return $this->successResponse(
            DeveloperMomentResource::collection($query->orderBy('created_at', 'desc')->get()),
            "Liste des developer moments chargée avec succès."
        );
    }

    public function store(StoreDeveloperMomentRequest $request)
    {
        $data = $request->validated();
        $uploadedPhoto = null;

        try {
            if ($request->hasFile('photo')) {
                $uploadedPhoto = $this->uploadImage($request->file('photo'), 'developer-moments');
                $data['photo'] = $uploadedPhoto;
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
        } catch (\Throwable $e) {
            if ($uploadedPhoto) {
                $this->deleteImage($uploadedPhoto, 'developer-moments');
            }

            throw $e;
        }
    }

    public function show(string $id)
    {
        $query = DeveloperMoment::query();

        if (! auth('sanctum')->check()) {
            $query->where('is_active', true);
        }

        $developerMoment = $query->find($id);

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
        $oldPhoto = $developerMoment->photo;
        $newPhoto = null;

        try {
            if ($request->hasFile('photo')) {
                $newPhoto = $this->uploadImage($request->file('photo'), 'developer-moments');
                $data['photo'] = $newPhoto;
            }

            $logData = [
                'old_value' => $developerMoment->toArray(),
                'new_value' => $data,
            ];

            $developerMoment->update($data);

            if ($oldPhoto && $newPhoto) {
                $this->deleteImage($oldPhoto, 'developer-moments');
            }

            logActivity(
                "Modification d'un developer moment",
                $logData,
                $developerMoment
            );

            return $this->successResponse(
                new DeveloperMomentResource($developerMoment),
                "Developer moment modifié avec succès."
            );
        } catch (\Throwable $e) {
            if ($newPhoto) {
                $this->deleteImage($newPhoto, 'developer-moments');
            }

            throw $e;
        }
    }

    public function destroy(string $id)
    {
        $developerMoment = DeveloperMoment::find($id);

        if (! $developerMoment) {
            return $this->errorResponse("Developer moment introuvable.");
        }

        $photo = $developerMoment->photo;

        logActivity(
            "Suppression d'un developer moment",
            $developerMoment->toArray(),
            $developerMoment
        );

        $developerMoment->delete();

        if ($photo) {
            $this->deleteImage($photo, 'developer-moments');
        }

        return $this->noContentSuccessResponse(
            "Developer moment supprimé avec succès."
        );
    }
}