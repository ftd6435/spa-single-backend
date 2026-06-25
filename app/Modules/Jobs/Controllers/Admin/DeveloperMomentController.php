<?php

namespace App\Modules\Jobs\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\DeveloperMoment;
use App\Modules\Jobs\Requests\StoreDeveloperMomentRequest;
use App\Modules\Jobs\Requests\UpdateDeveloperMomentRequest;
use App\Modules\Jobs\Resources\DeveloperMomentResource;

class DeveloperMomentController extends Controller
{
    public function index()
    {
        return DeveloperMomentResource::collection(
            DeveloperMoment::latest()->paginate(10)
        );
    }

    public function store(StoreDeveloperMomentRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')
                ->store('developer-moments/photos', 'public');
        }

        $developerMoment = DeveloperMoment::create($data);

        return new DeveloperMomentResource($developerMoment);
    }

    public function show(DeveloperMoment $developerMoment)
    {
        return new DeveloperMomentResource($developerMoment);
    }

    public function update(UpdateDeveloperMomentRequest $request, DeveloperMoment $developerMoment)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')
                ->store('developer-moments/photos', 'public');
        }

        $developerMoment->update($data);

        return new DeveloperMomentResource($developerMoment);
    }

    public function destroy(DeveloperMoment $developerMoment)
    {
        $developerMoment->delete();

        return response()->json([
            'message' => 'Developer moment deleted successfully.'
        ]);
    }
}