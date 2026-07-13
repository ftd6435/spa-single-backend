<?php

namespace App\Modules\Formation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Formation\Enums\FormationStatus;
use App\Modules\Formation\Models\Formation;
use App\Modules\Formation\Models\FormationImage;
use App\Modules\Formation\Requests\StoreFormationRequest;
use App\Modules\Formation\Requests\SwitchFormationStatusRequest;
use App\Modules\Formation\Requests\UpdateFormationRequest;
use App\Modules\Formation\Resources\AdminFormationResource;
use App\Modules\Formation\Resources\PublicFormationResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Stevebauman\Purify\Facades\Purify;

class FormationController extends Controller
{
    use ApiResponses, CloudflareUpload;

    public function index(Request $request)
    {
        $query = Formation::with('category')
            ->whereHas('category', fn ($category) => $category
                ->whereNull('deleted_at'))
            ->orderByDesc('date_debut');

        if ($request->filled('formation_category_id')) {
            $request->validate([
                'formation_category_id' => ['integer', 'exists:formation_categories,id'],
            ]);
            $query->where('formation_category_id', $request->integer('formation_category_id'));
        }

        if ($request->filled('status') && FormationStatus::tryFrom((string) $request->query('status'))) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return $this->successResponse(
            PublicFormationResource::collection($query->get()),
            'Liste des formations chargée avec succès.'
        );
    }

    public function show(string $formation)
    {
        $model = Formation::with('category')
            ->whereHas('category', fn ($category) => $category
                ->whereNull('deleted_at'))
            ->find($formation);

        if (! $model) {
            return $this->errorResponse('Formation introuvable.');
        }

        return $this->successResponse(
            PublicFormationResource::make($model),
            'Formation chargée avec succès.'
        );
    }

    public function store(StoreFormationRequest $request)
    {
        $data = $request->validated();
        $thumbnail = null;

        unset($data['thumbnail']);
        $data['description'] = Purify::clean($data['description']);
        $data['created_by'] = Auth::id();

        try {
            if ($request->hasFile('thumbnail')) {
                $thumbnail = $this->uploadImage($request->file('thumbnail'), Formation::THUMBNAIL_STORAGE_PATH);
                $data['thumbnail_path'] = $thumbnail;
            }

            $formation = DB::transaction(function () use ($data) {
                $formation = Formation::create($data);
                $this->syncContentImages($formation, Auth::id());

                return $formation;
            });
        } catch (\Throwable $exception) {
            if ($thumbnail) {
                $this->deleteImage($thumbnail, Formation::THUMBNAIL_STORAGE_PATH);
            }
            throw $exception;
        }

        logActivity("Création d'une formation", $data, $formation);

        return $this->successResponse(
            AdminFormationResource::make($formation->load(['category', 'createdBy', 'updatedBy'])),
            'Formation créée avec succès.'
        );
    }

    public function update(UpdateFormationRequest $request, string $formation)
    {
        $model = Formation::find($formation);
        if (! $model) {
            return $this->errorResponse('Formation introuvable.');
        }

        $data = $request->validated();
        $oldValues = $model->toArray();
        $oldThumbnail = $model->thumbnail_path;
        $newThumbnail = null;
        $removedImages = [];

        unset($data['thumbnail']);
        if (array_key_exists('description', $data)) {
            $data['description'] = Purify::clean($data['description']);
        }
        $data['updated_by'] = Auth::id();

        try {
            if ($request->hasFile('thumbnail')) {
                $newThumbnail = $this->uploadImage($request->file('thumbnail'), Formation::THUMBNAIL_STORAGE_PATH);
                $data['thumbnail_path'] = $newThumbnail;
            }

            DB::transaction(function () use ($model, $data, &$removedImages) {
                $model->update($data);

                if (array_key_exists('description', $data)) {
                    $removedImages = $this->syncContentImages($model, Auth::id());
                }
            });
        } catch (\Throwable $exception) {
            if ($newThumbnail) {
                $this->deleteImage($newThumbnail, Formation::THUMBNAIL_STORAGE_PATH);
            }
            throw $exception;
        }

        if ($newThumbnail && $oldThumbnail) {
            $this->deleteImage($oldThumbnail, Formation::THUMBNAIL_STORAGE_PATH);
        }
        foreach ($removedImages as $image) {
            $this->deleteImage($image, FormationImage::STORAGE_PATH);
        }

        logActivity("Modification d'une formation", [
            'old_value' => $oldValues,
            'new_value' => $data,
        ], $model);

        return $this->successResponse(
            AdminFormationResource::make($model->fresh()->load(['category', 'createdBy', 'updatedBy'])),
            'Formation modifiée avec succès.'
        );
    }

    public function switchStatus(SwitchFormationStatusRequest $request, string $formation)
    {
        $model = Formation::find($formation);
        if (! $model) {
            return $this->errorResponse('Formation introuvable.');
        }

        $data = $request->validated();
        $oldStatus = $model->status->value;
        $model->update([
            'status' => $data['status'],
            'updated_by' => Auth::id(),
        ]);

        logActivity("Changement du statut d'une formation", [
            'old_value' => ['status' => $oldStatus],
            'new_value' => ['status' => $model->status->value],
        ], $model);

        return $this->successResponse(
            AdminFormationResource::make($model->load(['category', 'createdBy', 'updatedBy'])),
            'Statut de la formation mis à jour avec succès.'
        );
    }

    public function switchState(string $formation)
    {
        $model = Formation::find($formation);
        if (! $model) {
            return $this->errorResponse('Formation introuvable.');
        }

        $oldState = $model->is_active;
        $model->update([
            'is_active' => ! $oldState,
            'updated_by' => Auth::id(),
        ]);

        logActivity("Changement de l'état d'une formation", [
            'old_value' => ['is_active' => $oldState],
            'new_value' => ['is_active' => $model->is_active],
        ], $model);

        return $this->successResponse(
            AdminFormationResource::make($model->load(['category', 'createdBy', 'updatedBy'])),
            'État de la formation mis à jour avec succès.'
        );
    }

    public function destroy(string $formation)
    {
        $model = Formation::find($formation);
        if (! $model) {
            return $this->errorResponse('Formation introuvable.');
        }

        logActivity("Suppression d'une formation", $model->toArray(), $model);
        $model->delete();

        return $this->noContentSuccessResponse('Formation supprimée avec succès.');
    }

    /**
     * @return array<int, string> Images retirées du HTML, à supprimer de R2 après commit.
     */
    private function syncContentImages(Formation $formation, int $userId): array
    {
        $referenced = $this->extractContentImageNames($formation->description);
        $current = $formation->images()->pluck('image_path')->all();
        $newImages = array_values(array_diff($referenced, $current));

        if ($newImages) {
            $eligible = FormationImage::query()
                ->whereNull('formation_id')
                ->where('uploaded_by', $userId)
                ->whereIn('image_path', $newImages)
                ->pluck('image_path')
                ->all();

            if (count($eligible) !== count($newImages)) {
                throw ValidationException::withMessages([
                    'description' => "Une ou plusieurs images de contenu n'ont pas été téléversées par cet utilisateur.",
                ]);
            }

            FormationImage::whereIn('image_path', $eligible)->update([
                'formation_id' => $formation->id,
            ]);
        }

        $removed = array_values(array_diff($current, $referenced));
        if ($removed) {
            FormationImage::where('formation_id', $formation->id)
                ->whereIn('image_path', $removed)
                ->delete();
        }

        return $removed;
    }

    /**
     * @return array<int, string>
     */
    private function extractContentImageNames(?string $html): array
    {
        if (! $html) {
            return [];
        }

        preg_match_all('#/formation-images/([A-Za-z0-9\-]+\.[A-Za-z0-9]+)#', $html, $matches);

        return array_values(array_unique($matches[1]));
    }
}
