<?php

namespace App\Modules\Website\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Website\Models\Service;
use App\Modules\Website\Requests\ServiceRequest;
use App\Modules\Website\Resources\ServiceResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    use ApiResponses, CloudflareUpload;

    public function index()
    {
        $services = Service::with('tags', 'createdBy', 'updatedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            ServiceResource::collection($services),
            'Liste des services chargée avec succès.'
        );
    }

    public function store(ServiceRequest $request)
    {
        $data = $request->validated();
        $tagIds = array_values(array_unique($data['tag_ids'] ?? []));
        $uploadedImage = null;

        unset($data['tag_ids']);

        try {
            if ($request->hasFile('image')) {
                $uploadedImage = $this->uploadImage($request->file('image'), 'services');
                $data['image_path'] = $uploadedImage;
            }

            unset($data['image']);

            $data['created_by'] = Auth::id();

            $service = DB::transaction(function () use ($data, $tagIds) {
                $service = Service::create($data);

                if (! empty($tagIds)) {
                    $pivotData = collect($tagIds)
                        ->mapWithKeys(fn ($tagId) => [
                            $tagId => ['created_by' => Auth::id()],
                        ])
                        ->all();

                    $service->tags()->sync($pivotData);
                }

                return $service;
            });
        } catch (\Throwable $e) {
            // Supprime le nouveau fichier si la création échoue après upload.
            if ($uploadedImage) {
                $this->deleteImage($uploadedImage, 'services');
            }

            throw $e;
        }

        logActivity("Création d'un service", $data + ['tag_ids' => $tagIds], $service);

        return $this->successResponse(
            ServiceResource::make($service->load('tags', 'createdBy', 'updatedBy')),
            'Service créé avec succès.'
        );
    }

    public function show(string $id)
    {
        $service = Service::with('tags', 'createdBy', 'updatedBy')->find($id);

        if (! $service) {
            return $this->errorResponse('Service introuvable.');
        }

        return $this->successResponse(
            ServiceResource::make($service),
            'Service chargé avec succès.'
        );
    }

    public function update(ServiceRequest $request, string $id)
    {
        $service = Service::find($id);

        if (! $service) {
            return $this->errorResponse('Service introuvable.');
        }

        $data = $request->validated();

        // tag_ids absent : on conserve les tags existants.
        $shouldSyncTags = array_key_exists('tag_ids', $data);
        $tagIds = array_values(array_unique($data['tag_ids'] ?? []));

        $oldImage = $service->image_path;
        $newImage = null;
        $oldValues = $service->load('tags')->toArray();

        unset($data['tag_ids']);

        try {
            if ($request->hasFile('image')) {
                $newImage = $this->uploadImage($request->file('image'), 'services');
                $data['image_path'] = $newImage;
            }

            unset($data['image']);

            $data['updated_by'] = Auth::id();

            DB::transaction(function () use ($service, $data, $shouldSyncTags, $tagIds) {
                $service->update($data);

                if ($shouldSyncTags) {
                    $existingTagIds = $service->tags()
                        ->pluck('tags.id')
                        ->all();

                    $pivotData = collect($tagIds)
                        ->mapWithKeys(fn ($tagId) => [
                            $tagId => in_array($tagId, $existingTagIds, true)
                                ? ['updated_by' => Auth::id()]
                                : ['created_by' => Auth::id()],
                        ])
                        ->all();

                    $service->tags()->sync($pivotData);
                }
            });
        } catch (\Throwable $e) {
            // Supprime le nouveau fichier si la mise à jour échoue.
            if ($newImage) {
                $this->deleteImage($newImage, 'services');
            }

            throw $e;
        }

        // Supprime l'ancienne image seulement après une mise à jour réussie.
        if ($newImage && $oldImage) {
            $this->deleteImage($oldImage, 'services');

            logActivity("Remplacement de l'image d'un service", [
                'old_image' => $oldImage,
                'new_image' => $newImage,
            ], $service);
        }

        $logData = [
            'old_value' => $oldValues,
            'new_value' => $data,
        ];

        if ($shouldSyncTags) {
            $logData['new_value']['tag_ids'] = $tagIds;
        }

        logActivity("Modification d'un service", $logData, $service);

        return $this->successResponse(
            ServiceResource::make($service->fresh()->load('tags', 'createdBy', 'updatedBy')),
            'Service modifié avec succès.'
        );
    }

    public function destroy(string $id)
    {
        $service = Service::find($id);

        if (! $service) {
            return $this->errorResponse('Service introuvable.');
        }

        $image = $service->image_path;

        logActivity("Suppression d'un service", $service->load('tags')->toArray(), $service);

        $service->delete();

        if ($image) {
            $this->deleteImage($image, 'services');
        }

        return $this->noContentSuccessResponse('Service supprimé avec succès.');
    }
}
