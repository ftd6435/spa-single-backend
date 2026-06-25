<?php

namespace App\Modules\Jobs\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\Page;
use App\Modules\Jobs\Requests\StorePageRequest;
use App\Modules\Jobs\Requests\UpdatePageRequest;
use App\Modules\Jobs\Resources\PageResource;

class PageController extends Controller
{
    public function index()
    {
        return PageResource::collection(
            Page::with('heroes')->latest()->paginate(10)
        );
    }

    public function store(StorePageRequest $request)
    {
        $page = Page::create($request->validated());

        return new PageResource($page);
    }

    public function show(Page $page)
    {
        return new PageResource($page->load('heroes'));
    }

    public function update(UpdatePageRequest $request, Page $page)
    {
        $page->update($request->validated());

        return new PageResource($page);
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return response()->json([
            'message' => 'Page deleted successfully.'
        ]);
    }
}