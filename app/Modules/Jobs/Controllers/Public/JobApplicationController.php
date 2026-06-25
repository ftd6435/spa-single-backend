<?php

namespace App\Modules\Jobs\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\JobApplication;
use App\Modules\Jobs\Requests\StoreJobApplicationRequest;
use App\Modules\Jobs\Resources\JobApplicationResource;

class JobApplicationController extends Controller
{
    public function store(StoreJobApplicationRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('cv_file')) {
            $data['cv_file'] = $request->file('cv_file')
                ->store('job-applications/cv', 'public');
        }

        $application = JobApplication::create($data);

        return new JobApplicationResource($application);
    }
}