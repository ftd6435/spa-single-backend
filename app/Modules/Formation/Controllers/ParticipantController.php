<?php

namespace App\Modules\Formation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Formation\Models\Participant;
use App\Modules\Formation\Requests\UpdateParticipantRequest;
use App\Modules\Formation\Resources\ParticipantResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    use ApiResponses, CloudflareUpload;

    public function index(Request $request)
    {
        $query = Participant::query()->orderByDesc('created_at');

        if ($request->query('trashed') === 'with') {
            $query->withTrashed();
        } elseif ($request->query('trashed') === 'only') {
            $query->onlyTrashed();
        }

        return $this->successResponse(
            ParticipantResource::collection($query->get()),
            'Liste des participants chargée avec succès.'
        );
    }

    public function show(string $participant)
    {
        $model = Participant::withTrashed()->find($participant);
        if (! $model) {
            return $this->errorResponse('Participant introuvable.');
        }

        return $this->successResponse(
            ParticipantResource::make($model),
            'Participant chargé avec succès.'
        );
    }

    public function update(UpdateParticipantRequest $request, string $participant)
    {
        $model = Participant::find($participant);
        if (! $model) {
            return $this->errorResponse('Participant introuvable.');
        }

        $data = $request->validated();
        $oldValues = $model->toArray();
        $oldAvatar = $model->avatar_path;
        $newAvatar = null;

        unset($data['avatar']);

        try {
            if ($request->hasFile('avatar')) {
                $newAvatar = $this->uploadImage($request->file('avatar'), Participant::AVATAR_STORAGE_PATH);
                $data['avatar_path'] = $newAvatar;
            }

            $model->update($data);
        } catch (\Throwable $exception) {
            if ($newAvatar) {
                $this->deleteImage($newAvatar, Participant::AVATAR_STORAGE_PATH);
            }
            throw $exception;
        }

        if ($newAvatar && $oldAvatar) {
            $this->deleteImage($oldAvatar, Participant::AVATAR_STORAGE_PATH);
        }

        logActivity("Modification d'un participant", [
            'old_value' => $oldValues,
            'new_value' => $data,
        ], $model);

        return $this->successResponse(
            ParticipantResource::make($model->fresh()),
            'Participant modifié avec succès.'
        );
    }

    public function destroy(string $participant)
    {
        $model = Participant::find($participant);
        if (! $model) {
            return $this->errorResponse('Participant introuvable.');
        }

        logActivity("Suppression d'un participant", $model->toArray(), $model);
        $model->delete();

        return $this->noContentSuccessResponse('Participant supprimé avec succès.');
    }
}
