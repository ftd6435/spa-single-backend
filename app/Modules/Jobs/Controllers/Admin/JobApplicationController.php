<?php

namespace App\Modules\Jobs\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\JobApplication;
use App\Modules\Jobs\Requests\UpdateJobApplicationRequest;
use App\Modules\Jobs\Resources\JobApplicationResource;

class JobApplicationController extends Controller
{
    public function index()
    {
        return JobApplicationResource::collection(
            JobApplication::with('jobOpening', 'processes')
                ->latest()
                ->paginate(10)
        );
    }

    public function show(JobApplication $jobApplication)
    {
        return new JobApplicationResource(
            $jobApplication->load('jobOpening', 'processes.processor')
        );
    }

    public function update(UpdateJobApplicationRequest $request, JobApplication $jobApplication)
    {
        $data = $request->validated();

        if ($request->hasFile('cv_file')) {
            $data['cv_file'] = $request->file('cv_file')
                ->store('job-applications/cv', 'public');
        }

        $jobApplication->update($data);

        return new JobApplicationResource($jobApplication);
    }

    public function destroy(JobApplication $jobApplication)
    {
        $jobApplication->delete();

        return response()->json([
            'message' => 'Job application deleted successfully.'
        ]);
    }
}