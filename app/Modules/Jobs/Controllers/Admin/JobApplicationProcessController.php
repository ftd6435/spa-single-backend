<?php

namespace App\Modules\Jobs\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\JobApplicationProcess;
use App\Modules\Jobs\Requests\StoreJobApplicationProcessRequest;
use App\Modules\Jobs\Requests\UpdateJobApplicationProcessRequest;
use App\Modules\Jobs\Resources\JobApplicationProcessResource;

class JobApplicationProcessController extends Controller
{
    public function index()
    {
        return JobApplicationProcessResource::collection(
            JobApplicationProcess::with('jobApplication', 'processor')
                ->latest()
                ->paginate(10)
        );
    }

    public function store(StoreJobApplicationProcessRequest $request)
    {
        $data = $request->validated();

        if (empty($data['processed_by']) && auth()->check()) {
            $data['processed_by'] = auth()->id();
        }

        $process = JobApplicationProcess::create($data);

        return new JobApplicationProcessResource($process);
    }

    public function show(JobApplicationProcess $jobApplicationProcess)
    {
        return new JobApplicationProcessResource(
            $jobApplicationProcess->load('jobApplication', 'processor')
        );
    }

    public function update(UpdateJobApplicationProcessRequest $request, JobApplicationProcess $jobApplicationProcess)
    {
        $jobApplicationProcess->update($request->validated());

        return new JobApplicationProcessResource($jobApplicationProcess);
    }

    public function destroy(JobApplicationProcess $jobApplicationProcess)
    {
        $jobApplicationProcess->delete();

        return response()->json([
            'message' => 'Application process deleted successfully.'
        ]);
    }
}