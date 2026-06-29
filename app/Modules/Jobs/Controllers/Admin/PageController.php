<?php

namespace App\Modules\Jobs\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\Page;
use App\Modules\Jobs\Requests\StorePageRequest;
use App\Modules\Jobs\Requests\UpdatePageRequest;
use App\Modules\Jobs\Resources\PageResource;
use App\Traits\ApiResponses;

class PageController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $pages = Page::with('heroes')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            PageResource::collection($pages),
            "Liste des pages chargée avec succès."
        );
    }

    public function store(StorePageRequest $request)
    {
        $data = $request->validated();

        $page = Page::create($data);

        logActivity(
            "Création d'une page",
            $data,
            $page
        );

        return $this->successResponse(
            new PageResource($page),
            "Page créée avec succès."
        );
    }

    public function show(string $id)
    {
        $page = Page::with('heroes')->find($id);

        if (! $page) {
            return $this->errorResponse("Page introuvable.");
        }

        return $this->successResponse(
            new PageResource($page),
            "Page chargée avec succès."
        );
    }

    public function switchStatus(string $id)
    {
        $page = Page::find($id);

        if (! $page) {
            return $this->errorResponse("Page introuvable.");
        }

        $oldValue = $page->toArray();

        $page->is_active = ! $page->is_active;
        $page->save();

        $logData = [
            'old_value' => $oldValue,
            'new_value' => $page->toArray(),
        ];

        logActivity(
            "Changement du statut d'une page",
            $logData,
            $page
        );

        return $this->noContentSuccessResponse(
            "Statut de la page mis à jour avec succès."
        );
    }

    public function update(UpdatePageRequest $request, string $id)
    {
        $page = Page::find($id);

        if (! $page) {
            return $this->errorResponse("Page introuvable.");
        }

        $data = $request->validated();

        $logData = [
            'old_value' => $page->toArray(),
            'new_value' => $data,
        ];

        $page->update($data);

        logActivity(
            "Modification d'une page",
            $logData,
            $page
        );

        return $this->successResponse(
            new PageResource($page->load('heroes')),
            "Page modifiée avec succès."
        );
    }

    public function destroy(string $id)
    {
        $page = Page::find($id);

        if (! $page) {
            return $this->errorResponse("Page introuvable.");
        }

        logActivity(
            "Suppression d'une page",
            $page->toArray(),
            $page
        );

        $page->delete();

        return $this->noContentSuccessResponse(
            "Page supprimée avec succès."
        );
    }
}