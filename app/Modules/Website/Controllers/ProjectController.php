<?php

namespace App\Modules\Website\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Website\Models\Project;
use App\Modules\Website\Requests\ProjectRequest;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $projects = Project::with('category', 'service', 'createdBy', 'updatedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($projects, 'Liste des projets chargée avec succès.');
    }

    public function publicIndex(Request $request)
    {
        $filters = $request->validate([
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'service_id' => ['sometimes', 'integer', 'exists:services,id'],
        ]);

        $projects = Project::query()
            ->select(
                'id',
                'category_id',
                'service_id',
                'title',
                'short_description',
                'description',
                'demo_link'
            )
            ->with([
                'category:id,libelle,description,status',
                'service:id,icon,image_path,title,short_description,description,benefits',
            ])
            ->when(
                isset($filters['category_id']),
                fn ($query) => $query->where('category_id', $filters['category_id'])
            )
            ->when(
                isset($filters['service_id']),
                fn ($query) => $query->where('service_id', $filters['service_id'])
            )
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($projects, 'Liste des projets chargée avec succès.');
    }

    public function store(ProjectRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $project = Project::create($data);

        logActivity("Création d'un projet", $data, $project);

        return $this->successResponse(
            $project->load('category', 'service', 'createdBy', 'updatedBy'),
            'Projet créé avec succès.'
        );
    }

    public function show(string $id)
    {
        $project = Project::with('category', 'service', 'createdBy', 'updatedBy')->find($id);

        if (! $project) {
            return $this->errorResponse('Projet introuvable.');
        }

        return $this->successResponse($project, 'Projet chargé avec succès.');
    }

    public function publicShow(string $id)
    {
        $project = Project::query()
            ->select(
                'id',
                'category_id',
                'service_id',
                'title',
                'short_description',
                'description',
                'demo_link'
            )
            ->with([
                'category:id,libelle,description,status',
                'service:id,icon,image_path,title,short_description,description,benefits',
            ])
            ->find($id);

        if (! $project) {
            return $this->errorResponse('Projet introuvable.');
        }

        return $this->successResponse($project, 'Projet chargé avec succès.');
    }

    public function update(ProjectRequest $request, string $id)
    {
        $project = Project::find($id);

        if (! $project) {
            return $this->errorResponse('Projet introuvable.');
        }

        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        $logData = [
            'old_value' => $project->toArray(),
            'new_value' => $data,
        ];

        $project->update($data);

        logActivity("Modification d'un projet", $logData, $project);

        return $this->successResponse(
            $project->fresh()->load('category', 'service', 'createdBy', 'updatedBy'),
            'Projet modifié avec succès.'
        );
    }

    public function destroy(string $id)
    {
        $project = Project::find($id);

        if (! $project) {
            return $this->errorResponse('Projet introuvable.');
        }

        logActivity("Suppression d'un projet", $project->toArray(), $project);

        $project->delete();

        return $this->noContentSuccessResponse('Projet supprimé avec succès.');
    }
}
