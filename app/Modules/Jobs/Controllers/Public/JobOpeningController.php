<?php

namespace App\Modules\Jobs\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\JobOpening;
use App\Modules\Jobs\Resources\JobOpeningResource;

class JobOpeningController extends Controller
{
    public function index()
    {
        return JobOpeningResource::collection(
            JobOpening::where('is_active', true)
                ->latest()
                ->paginate(10)
        );
    }

    public function show(JobOpening $jobOpening)
    {
        return new JobOpeningResource($jobOpening);
    }
}