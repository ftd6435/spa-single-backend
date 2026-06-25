<?php

namespace App\Modules\Website\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Website\Models\Vision;
use App\Modules\Website\Requests\VisionRequest;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class VisionController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $visions = Vision::with('createdBy', 'updatedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($visions, 'Liste des visions chargée avec succès.');
    }

    public function publicIndex()
    {
        $visions = Vision::query()
            ->select('id', 'title', 'description', 'author')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($visions, 'Liste des visions chargée avec succès.');
    }

    public function store(VisionRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $vision = Vision::create($data);

        logActivity("Création d'une vision", $data, $vision);

        return $this->successResponse(
            $vision->load('createdBy', 'updatedBy'),
            'Vision créée avec succès.'
        );
    }

    public function show(string $id)
    {
        $vision = Vision::with('createdBy', 'updatedBy')->find($id);

        if (! $vision) {
            return $this->errorResponse('Vision introuvable.');
        }

        return $this->successResponse($vision, 'Vision chargée avec succès.');
    }

    public function update(VisionRequest $request, string $id)
    {
        $vision = Vision::find($id);

        if (! $vision) {
            return $this->errorResponse('Vision introuvable.');
        }

        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        $logData = [
            'old_value' => $vision->toArray(),
            'new_value' => $data,
        ];

        $vision->update($data);

        logActivity("Modification d'une vision", $logData, $vision);

        return $this->successResponse(
            $vision->fresh()->load('createdBy', 'updatedBy'),
            'Vision modifiée avec succès.'
        );
    }

    public function destroy(string $id)
    {
        $vision = Vision::find($id);

        if (! $vision) {
            return $this->errorResponse('Vision introuvable.');
        }

        logActivity("Suppression d'une vision", $vision->toArray(), $vision);

        $vision->delete();

        return $this->noContentSuccessResponse('Vision supprimée avec succès.');
    }
}
