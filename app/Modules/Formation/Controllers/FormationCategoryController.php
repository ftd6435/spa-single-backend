<?php

namespace App\Modules\Formation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Formation\Models\FormationCategory;
use App\Modules\Formation\Requests\StoreFormationCategoryRequest;
use App\Modules\Formation\Requests\UpdateFormationCategoryRequest;
use App\Modules\Formation\Resources\AdminFormationCategoryResource;
use App\Modules\Formation\Resources\PublicFormationCategoryResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormationCategoryController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $categories = FormationCategory::query()
            ->where('is_active', true)
            ->orderBy('libelle')
            ->get();

        return $this->successResponse(
            PublicFormationCategoryResource::collection($categories),
            'Liste des catégories de formation chargée avec succès.'
        );
    }

    public function adminIndex(Request $request)
    {
        $query = FormationCategory::with(['createdBy', 'updatedBy'])->orderByDesc('created_at');

        if ($request->query('trashed') === 'with') {
            $query->withTrashed();
        } elseif ($request->query('trashed') === 'only') {
            $query->onlyTrashed();
        }

        return $this->successResponse(
            AdminFormationCategoryResource::collection($query->get()),
            'Liste des catégories de formation chargée avec succès.'
        );
    }

    public function store(StoreFormationCategoryRequest $request)
    {
        $data = $request->validated() + ['created_by' => Auth::id()];
        $category = FormationCategory::create($data);

        logActivity("Création d'une catégorie de formation", $data, $category);

        return $this->successResponse(
            AdminFormationCategoryResource::make($category->load('createdBy', 'updatedBy')),
            'Catégorie de formation créée avec succès.'
        );
    }

    public function adminShow(string $formationCategory)
    {
        $category = FormationCategory::withTrashed()
            ->with(['createdBy', 'updatedBy'])
            ->find($formationCategory);

        if (! $category) {
            return $this->errorResponse('Catégorie de formation introuvable.');
        }

        return $this->successResponse(
            AdminFormationCategoryResource::make($category),
            'Catégorie de formation chargée avec succès.'
        );
    }

    public function update(UpdateFormationCategoryRequest $request, string $formationCategory)
    {
        $category = FormationCategory::find($formationCategory);

        if (! $category) {
            return $this->errorResponse('Catégorie de formation introuvable.');
        }

        $data = $request->validated() + ['updated_by' => Auth::id()];
        $oldValues = $category->toArray();
        $category->update($data);

        logActivity("Modification d'une catégorie de formation", [
            'old_value' => $oldValues,
            'new_value' => $data,
        ], $category);

        return $this->successResponse(
            AdminFormationCategoryResource::make($category->fresh()->load('createdBy', 'updatedBy')),
            'Catégorie de formation modifiée avec succès.'
        );
    }

    public function destroy(string $formationCategory)
    {
        $category = FormationCategory::find($formationCategory);

        if (! $category) {
            return $this->errorResponse('Catégorie de formation introuvable.');
        }

        logActivity("Suppression d'une catégorie de formation", $category->toArray(), $category);
        $category->delete();

        return $this->noContentSuccessResponse('Catégorie de formation supprimée avec succès.');
    }
}
