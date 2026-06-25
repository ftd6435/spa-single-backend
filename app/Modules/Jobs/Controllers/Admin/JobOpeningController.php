<?php

namespace App\Modules\Jobs\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\JobOpening;
use App\Modules\Jobs\Requests\StoreJobOpeningRequest;
use App\Modules\Jobs\Requests\UpdateJobOpeningRequest;
use App\Modules\Jobs\Resources\JobOpeningResource;

class JobOpeningController extends Controller
{
    public function index()
    {
        return JobOpeningResource::collection(
            JobOpening::with('applications')->latest()->paginate(10)
        );
    }

    public function store(StoreJobOpeningRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('job-openings/images', 'public');
        }

        $jobOpening = JobOpening::create($data);

        return new JobOpeningResource($jobOpening);
    }

    public function show(JobOpening $jobOpening)
    {
        return new JobOpeningResource(
            $jobOpening->load('applications')
        );
    }

    public function update(UpdateJobOpeningRequest $request, JobOpening $jobOpening)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('job-openings/images', 'public');
        }

        $jobOpening->update($data);

        return new JobOpeningResource($jobOpening);
    }

    public function destroy(JobOpening $jobOpening)
    {
        $jobOpening->delete();

        return response()->json([
            'message' => 'Job opening deleted successfully.'
        ]);
    }
}