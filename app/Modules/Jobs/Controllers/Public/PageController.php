<?php

namespace App\Modules\Jobs\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\Page;
use App\Modules\Jobs\Resources\PageResource;
use App\Traits\ApiResponses;

class PageController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $pages = Page::with('heroes')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            PageResource::collection($pages),
            "Liste des pages chargée avec succès."
        );
    }

    public function show(string $id)
    {
        $page = Page::with('heroes')
            ->where('is_active', true)
            ->find($id);

        if (! $page) {
            return $this->errorResponse("Page introuvable.");
        }

        return $this->successResponse(
            new PageResource($page),
            "Page chargée avec succès."
        );
    }
}