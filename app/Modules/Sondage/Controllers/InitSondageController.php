<?php

namespace App\Modules\Sondage\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sondage\Models\InitSondage;
use App\Modules\Sondage\Requests\StoreInitSondageRequest;
use App\Modules\Sondage\Requests\UpdateInitSondageRequest;
use App\Modules\Sondage\Resources\InitSondageResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;

class InitSondageController extends Controller
{
    use ApiResponses, CloudflareUpload;

    // Route publique — les visiteurs ne voient que les sondages actifs
    public function index()
    {
        $query = InitSondage::with('competition', 'rencontres');

        if (! auth('sanctum')->check()) {
            $query->where('is_active', true);
        }

        return $this->successResponse(
            InitSondageResource::collection($query->orderBy('created_at', 'desc')->get()),
            "Liste des sondages chargée avec succès."
        );
    }

    // Route admin — création d'un nouveau sondage
    public function store(StoreInitSondageRequest $request)
    {
        $data = $request->validated();
        $uploadedImage = null;

        try {
            if ($request->hasFile('image')) {
                $uploadedImage = $this->uploadImage($request->file('image'), 'sondages');
                $data['image'] = $uploadedImage;
            }

            $initSondage = InitSondage::create($data);

            logActivity("Création d'un sondage", $data, $initSondage);

            return $this->successResponse(
                new InitSondageResource($initSondage->load('competition')),
                "Sondage créé avec succès."
            );
        } catch (\Throwable $e) {
            if ($uploadedImage) {
                $this->deleteImage($uploadedImage, 'sondages');
            }

            throw $e;
        }
    }

    // Route publique — détail d'un sondage
    public function show(string $id)
    {
        $query = InitSondage::with('competition', 'rencontres');

        if (! auth('sanctum')->check()) {
            $query->where('is_active', true);
        }

        $initSondage = $query->find($id);

        if (! $initSondage) {
            return $this->errorResponse("Sondage introuvable.");
        }

        return $this->successResponse(new InitSondageResource($initSondage), "Sondage chargé avec succès.");
    }

    // Route admin — modification d'un sondage existant
    public function update(UpdateInitSondageRequest $request, string $id)
    {
        $initSondage = InitSondage::find($id);

        if (! $initSondage) {
            return $this->errorResponse("Sondage introuvable.");
        }

        $data = $request->validated();
        $oldImage = $initSondage->image;
        $newImage = null;

        try {
            if ($request->hasFile('image')) {
                $newImage = $this->uploadImage($request->file('image'), 'sondages');
                $data['image'] = $newImage;
            }

            $logData = [
                'old_value' => $initSondage->toArray(),
                'new_value' => $data,
            ];

            $initSondage->update($data);

            if ($oldImage && $newImage) {
                $this->deleteImage($oldImage, 'sondages');
            }

            logActivity("Modification d'un sondage", $logData, $initSondage);

            return $this->successResponse(
                new InitSondageResource($initSondage->load('competition')),
                "Sondage modifié avec succès."
            );
        } catch (\Throwable $e) {
            if ($newImage) {
                $this->deleteImage($newImage, 'sondages');
            }

            throw $e;
        }
    }

    // Route admin — activer/désactiver un sondage
    public function switchStatus(string $id)
    {
        $initSondage = InitSondage::find($id);

        if (! $initSondage) {
            return $this->errorResponse("Sondage introuvable.");
        }

        $initSondage->is_active = ! $initSondage->is_active;
        $initSondage->save();

        logActivity("Changement du statut d'un sondage", $initSondage->toArray(), $initSondage);

        return $this->noContentSuccessResponse("Statut du sondage mis à jour avec succès.");
    }

    // Route admin — suppression définitive d'un sondage
    public function destroy(string $id)
    {
        $initSondage = InitSondage::find($id);

        if (! $initSondage) {
            return $this->errorResponse("Sondage introuvable.");
        }

        $image = $initSondage->image;

        logActivity("Suppression d'un sondage", $initSondage->toArray(), $initSondage);
        $initSondage->delete();

        if ($image) {
            $this->deleteImage($image, 'sondages');
        }

        return $this->noContentSuccessResponse("Sondage supprimé avec succès.");
    }
}
