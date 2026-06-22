<?php

namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Models\Category;
use App\Modules\Settings\Requests\CategoryRequest;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $categories = Category::with('createdBy', 'updatedBy')->orderBy('created_at', 'desc')->get();

        return $this->successResponse($categories, "Liste des catégories loader avec succès.");
    }

    public function store(CategoryRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $category = Category::create($data);

        logActivity("Création d'une catégorie", $data, $category);

        return $this->successResponse($category, "Catégorie créée avec succès.");
    }

    public function show(string $id)
    {
        $category = Category::find($id);

        if (! $category) {
            return $this->errorResponse("Catégorie introuvable");
        }

        return $this->successResponse($category, "Catégorie demandée loader avec succès");
    }

    public function switchStatus(string $id)
    {
        $category = Category::find($id);

        if (! $category) {
            return $this->errorResponse("Catégorie introuvable");
        }

        $category->status = ! $category->status;
        $category->save();

        return $this->noContentSuccessResponse("Status de la catégorie mis a jour avec succès");
    }

    public function update(CategoryRequest $request, string $id)
    {
        $category = Category::find($id);

        if (! $category) {
            return $this->errorResponse("Catégorie introuvable");
        }

        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        $logData = [
            'old_value' => $category,
            'new_value' => $data,
        ];

        $category = $category->update($data);

        logActivity("Modification d'une catégorie", $logData, $category);

        return $this->successResponse($category, 'Catégorie modifiée avec succès.');
    }

    public function destroy(string $id)
    {
        $category = Category::find($id);

        if (! $category) {
            return $this->errorResponse("Catégorie introuvable");
        }

        logActivity("Suppression d'une catégorie", $category->toArray(), $category);
        $category->delete();

        return $this->noContentSuccessResponse("Catégorie supprimée avec succès");
    }
}
