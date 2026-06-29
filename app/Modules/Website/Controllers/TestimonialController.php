<?php

namespace App\Modules\Website\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Website\Models\Testimonial;
use App\Modules\Website\Requests\TestimonialRequest;
use App\Modules\Website\Resources\TestimonialResource;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class TestimonialController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $testimonials = Testimonial::with('client', 'project', 'createdBy', 'updatedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            TestimonialResource::collection($testimonials),
            'Liste des témoignages chargée avec succès.'
        );
    }

    public function publicIndex()
    {
        $testimonials = Testimonial::query()
            ->select('id', 'project_id', 'client_id', 'content')
            ->with([
                'client:id,first_name,last_name,job_title',
                'project:id,title,short_description',
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->each->makeHidden(['project_id', 'client_id']);

        return $this->successResponse(
            TestimonialResource::collection($testimonials),
            'Liste des témoignages chargée avec succès.'
        );
    }

    public function store(TestimonialRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $testimonial = Testimonial::create($data);

        logActivity("Création d'un témoignage", $data, $testimonial);

        return $this->successResponse(
            TestimonialResource::make($testimonial->load('client', 'project', 'createdBy', 'updatedBy')),
            'Témoignage créé avec succès.'
        );
    }

    public function show(string $id)
    {
        $testimonial = Testimonial::with('client', 'project', 'createdBy', 'updatedBy')->find($id);

        if (! $testimonial) {
            return $this->errorResponse('Témoignage introuvable.');
        }

        return $this->successResponse(
            TestimonialResource::make($testimonial),
            'Témoignage chargé avec succès.'
        );
    }

    public function update(TestimonialRequest $request, string $id)
    {
        $testimonial = Testimonial::find($id);

        if (! $testimonial) {
            return $this->errorResponse('Témoignage introuvable.');
        }

        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        $logData = [
            'old_value' => $testimonial->toArray(),
            'new_value' => $data,
        ];

        $testimonial->update($data);

        logActivity("Modification d'un témoignage", $logData, $testimonial);

        return $this->successResponse(
            TestimonialResource::make($testimonial->fresh()->load('client', 'project', 'createdBy', 'updatedBy')),
            'Témoignage modifié avec succès.'
        );
    }

    public function destroy(string $id)
    {
        $testimonial = Testimonial::find($id);

        if (! $testimonial) {
            return $this->errorResponse('Témoignage introuvable.');
        }

        logActivity("Suppression d'un témoignage", $testimonial->toArray(), $testimonial);

        $testimonial->delete();

        return $this->noContentSuccessResponse('Témoignage supprimé avec succès.');
    }
}
