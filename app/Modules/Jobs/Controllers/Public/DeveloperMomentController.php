<?php

namespace App\Modules\Jobs\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\DeveloperMoment;
use App\Modules\Jobs\Resources\DeveloperMomentResource;

class DeveloperMomentController extends Controller
{
    public function index()
    {
        return DeveloperMomentResource::collection(
            DeveloperMoment::where('is_active', true)->latest()->get()
        );
    }

    public function show(DeveloperMoment $developerMoment)
    {
        return new DeveloperMomentResource($developerMoment);
    }
}