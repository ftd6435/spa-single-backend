<?php

namespace App\Modules\Jobs\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\Hero;
use App\Modules\Jobs\Requests\StoreHeroRequest;
use App\Modules\Jobs\Requests\UpdateHeroRequest;
use App\Modules\Jobs\Resources\HeroResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;

class HeroController extends Controller
{
    use ApiResponses, CloudflareUpload;

    public function index()
    {
        $query = Hero::with('page');

        if (! auth('sanctum')->check()) {
            $query->where('is_active', true);
        }

        return $this->successResponse(
            HeroResource::collection($query->orderBy('created_at', 'desc')->get()),
            "Liste des heroes chargée avec succès."
        );
    }

    public function store(StoreHeroRequest $request)
    {
        $data = $request->validated();
        $uploadedFile = null;

        try {
            if ($request->hasFile('file')) {
                $uploadedFile = $this->uploadFile($request->file('file'), 'heroes');
                $data['file'] = $uploadedFile;
            }

            $hero = Hero::create($data);

            logActivity(
                "Création d'un hero",
                $data,
                $hero
            );

            return $this->successResponse(
                new HeroResource($hero->load('page')),
                "Hero créé avec succès."
            );
        } catch (\Throwable $e) {
            if ($uploadedFile) {
                $this->deleteFile($uploadedFile, 'heroes');
            }

            throw $e;
        }
    }

    public function show(string $id)
    {
        $query = Hero::with('page');

        if (! auth('sanctum')->check()) {
            $query->where('is_active', true);
        }

        $hero = $query->find($id);

        if (! $hero) {
            return $this->errorResponse("Hero introuvable.");
        }

        return $this->successResponse(
            new HeroResource($hero),
            "Hero chargé avec succès."
        );
    }

    public function switchStatus(string $id)
    {
        $hero = Hero::find($id);

        if (! $hero) {
            return $this->errorResponse("Hero introuvable.");
        }

        $oldValue = $hero->toArray();

        $hero->is_active = ! $hero->is_active;
        $hero->save();

        $logData = [
            'old_value' => $oldValue,
            'new_value' => $hero->toArray(),
        ];

        logActivity(
            "Changement du statut d'un hero",
            $logData,
            $hero
        );

        return $this->noContentSuccessResponse(
            "Statut du hero mis à jour avec succès."
        );
    }

    public function update(UpdateHeroRequest $request, string $id)
    {
        $hero = Hero::find($id);

        if (! $hero) {
            return $this->errorResponse("Hero introuvable.");
        }

        $data = $request->validated();
        $oldFile = $hero->file;
        $newFile = null;

        try {
            if ($request->hasFile('file')) {
                $newFile = $this->uploadFile($request->file('file'), 'heroes');
                $data['file'] = $newFile;
            }

            $logData = [
                'old_value' => $hero->toArray(),
                'new_value' => $data,
            ];

            $hero->update($data);

            if ($newFile && $oldFile) {
                $this->deleteFile($oldFile, 'heroes');
            }

            logActivity(
                "Modification d'un hero",
                $logData,
                $hero
            );

            return $this->successResponse(
                new HeroResource($hero->load('page')),
                "Hero modifié avec succès."
            );
        } catch (\Throwable $e) {
            if ($newFile) {
                $this->deleteFile($newFile, 'heroes');
            }

            throw $e;
        }
    }

    public function destroy(string $id)
    {
        $hero = Hero::find($id);

        if (! $hero) {
            return $this->errorResponse("Hero introuvable.");
        }

        $file = $hero->file;

        logActivity(
            "Suppression d'un hero",
            $hero->toArray(),
            $hero
        );

        $hero->delete();

        if ($file) {
            $this->deleteFile($file, 'heroes');
        }

        return $this->noContentSuccessResponse(
            "Hero supprimé avec succès."
        );
    }
}