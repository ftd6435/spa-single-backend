<?php

namespace App\Modules\Formation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Formation\Enums\FormationStatus;
use App\Modules\Formation\Models\Formation;
use App\Modules\Formation\Models\Participant;
use App\Modules\Formation\Models\Participation;
use App\Modules\Formation\Requests\RegisterParticipationRequest;
use App\Modules\Formation\Requests\SwitchParticipationStatusRequest;
use App\Modules\Formation\Resources\ParticipationResource;
use App\Modules\Formation\Resources\PublicParticipationRegistrationResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ParticipationController extends Controller
{
    use ApiResponses, CloudflareUpload;

    public function store(RegisterParticipationRequest $request, string $formation)
    {
        $data = $request->validated();
        $uploadedAvatar = null;

        try {
            $participation = DB::transaction(function () use ($request, $formation, $data, &$uploadedAvatar) {
                $lockedFormation = Formation::query()
                    ->whereKey($formation)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedFormation) {
                    throw ValidationException::withMessages(['formation' => 'Formation introuvable.']);
                }

                $this->ensureRegistrationIsAllowed($lockedFormation);

                $participant = Participant::withTrashed()
                    ->where('telephone', $data['telephone'])
                    ->lockForUpdate()
                    ->first();

                if ($participant?->trashed()) {
                    $participant->restore();
                }

                $existingParticipation = $participant
                    ? Participation::withTrashed()
                        ->where('formation_id', $lockedFormation->id)
                        ->where('participant_id', $participant->id)
                        ->lockForUpdate()
                        ->first()
                    : null;

                if ($existingParticipation && ! $existingParticipation->trashed()) {
                    throw ValidationException::withMessages([
                        'telephone' => 'Ce participant est déjà inscrit à cette formation.',
                    ]);
                }

                if ($lockedFormation->participations()->count() >= $lockedFormation->nombre_places) {
                    throw ValidationException::withMessages([
                        'formation' => 'Le nombre de places de cette formation est atteint.',
                    ]);
                }

                if (! $participant) {
                    $participantData = [
                        'nom' => $data['nom'],
                        'prenom' => $data['prenom'],
                        'telephone' => $data['telephone'],
                        'adresse' => $data['adresse'] ?? null,
                    ];

                    if ($request->hasFile('avatar')) {
                        $uploadedAvatar = $this->uploadImage($request->file('avatar'), Participant::AVATAR_STORAGE_PATH);
                        $participantData['avatar_path'] = $uploadedAvatar;
                    }

                    $participant = Participant::create($participantData);
                }

                if ($existingParticipation) {
                    $existingParticipation->restore();
                    $participation = $existingParticipation;
                } else {
                    $participation = Participation::create([
                        'formation_id' => $lockedFormation->id,
                        'participant_id' => $participant->id,
                        'frais_inscription_requis' => $lockedFormation->frais_inscription,
                        'frais_inscription_paye' => 0,
                    ]);
                }

                return $participation;
            });
        } catch (\Throwable $exception) {
            if ($uploadedAvatar) {
                $this->deleteImage($uploadedAvatar, Participant::AVATAR_STORAGE_PATH);
            }
            throw $exception;
        }

        return $this->successResponse(
            PublicParticipationRegistrationResource::make($participation),
            'Inscription enregistrée avec succès.'
        );
    }

    public function index(Request $request)
    {
        $query = Participation::with(['formation.category', 'participant'])
            ->orderByDesc('created_at');

        if ($request->query('trashed') === 'with') {
            $query->withTrashed();
        } elseif ($request->query('trashed') === 'only') {
            $query->onlyTrashed();
        }

        if ($request->filled('formation_id')) {
            $query->where('formation_id', $request->integer('formation_id'));
        }

        return $this->successResponse(
            ParticipationResource::collection($query->get()),
            'Liste des participations chargée avec succès.'
        );
    }

    public function show(string $participation)
    {
        $model = Participation::withTrashed()
            ->with(['formation.category', 'participant', 'payments' => fn ($query) => $query->withTrashed()])
            ->find($participation);

        if (! $model) {
            return $this->errorResponse('Participation introuvable.');
        }

        return $this->successResponse(
            ParticipationResource::make($model),
            'Participation chargée avec succès.'
        );
    }

    public function switchStatus(SwitchParticipationStatusRequest $request, string $participation)
    {
        $model = Participation::find($participation);
        if (! $model) {
            return $this->errorResponse('Participation introuvable.');
        }

        $oldStatus = $model->status->value;
        $model->update(['status' => $request->validated('status')]);

        logActivity("Changement du statut d'une participation", [
            'old_value' => ['status' => $oldStatus],
            'new_value' => ['status' => $model->status->value],
        ], $model);

        return $this->successResponse(
            ParticipationResource::make($model->load(['formation.category', 'participant'])),
            'Statut de la participation mis à jour avec succès.'
        );
    }

    public function destroy(string $participation)
    {
        $model = Participation::find($participation);
        if (! $model) {
            return $this->errorResponse('Participation introuvable.');
        }

        logActivity("Suppression d'une participation", $model->toArray(), $model);
        $model->delete();

        return $this->noContentSuccessResponse('Participation supprimée avec succès.');
    }

    private function ensureRegistrationIsAllowed(Formation $formation): void
    {
        $formation->load('category');

        if (! $formation->category || $formation->category->trashed() || ! $formation->category->is_active) {
            throw ValidationException::withMessages([
                'formation' => 'La catégorie de cette formation est indisponible.',
            ]);
        }

        if (! $formation->is_active) {
            throw ValidationException::withMessages([
                'formation' => 'Cette formation est inactive.',
            ]);
        }

        if (! in_array($formation->status, [FormationStatus::EnAttente, FormationStatus::EnCours], true)) {
            throw ValidationException::withMessages([
                'formation' => 'Les inscriptions ne sont pas ouvertes pour cette formation.',
            ]);
        }
    }
}
