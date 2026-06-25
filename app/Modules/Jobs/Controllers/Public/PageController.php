<?php

namespace App\Modules\Jobs\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\Page;
use App\Modules\Jobs\Resources\PageResource;

class PageController extends Controller
{
    public function index()
    {
        return PageResource::collection(
            Page::with('heroes')
                ->where('is_active', true)
                ->latest()
                ->get()
        );
    }

    public function show(Page $page)
    {
        return new PageResource($page->load('heroes'));
    }
}