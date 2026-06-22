<?php

namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Models\Tag;
use App\Modules\Settings\Requests\TagRequest;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $tags = Tag::with('createdBy', 'updatedBy')->orderBy('created_at', 'desc')->get();

        return $this->successResponse($tags, "Liste des tags loader avec succès.");
    }

    public function store(TagRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $tag = Tag::create($data);

        logActivity("Création d'un tag", $data, $tag);

        return $this->successResponse($tag, "Tag créé avec succès.");
    }

    public function show(string $id)
    {
        $tag = Tag::find($id);

        if (! $tag) {
            return $this->errorResponse("Tag introuvable");
        }

        return $this->successResponse($tag, "Tag demandée loader avec succès");
    }

    public function switchStatus(string $id)
    {
        $tag = Tag::find($id);

        if (! $tag) {
            return $this->errorResponse("Tag introuvable");
        }

        $tag->status = ! $tag->status;
        $tag->save();

        return $this->noContentSuccessResponse("Status de la tag mis a jour avec succès");
    }

    public function update(TagRequest $request, string $id)
    {
        $tag = Tag::find($id);

        if (! $tag) {
            return $this->errorResponse("Tag introuvable");
        }

        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        $logData = [
            'old_value' => $tag,
            'new_value' => $data,
        ];

        $tag = $tag->update($data);

        logActivity("Modification d'un tag", $logData, $tag);

        return $this->successResponse($tag, 'Tag modifié avec succès.');
    }

    public function destroy(string $id)
    {
        $tag = Tag::find($id);

        if (! $tag) {
            return $this->errorResponse("Tag introuvable");
        }

        logActivity("Suppression d'un tag", $tag->toArray(), $tag);
        $tag->delete();

        return $this->noContentSuccessResponse("Tag supprimé avec succès");
    }
}
