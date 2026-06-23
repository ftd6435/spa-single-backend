<?php

namespace App\Modules\Website\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Website\Models\Partner;
use App\Modules\Website\Requests\PartnerRequest;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class PartnerController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $partners = Partner::with('createdBy', 'updatedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($partners, "Liste des partenaires chargée avec succès.");
    }

    public function store(PartnerRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $partner = Partner::create($data);

        logActivity("Création d'un partenaire", $data, $partner);

        return $this->successResponse($partner, "Partenaire créé avec succès.");
    }

    public function show(string $id)
    {
        $partner = Partner::with('createdBy', 'updatedBy')->find($id);

        if (! $partner) {
            return $this->errorResponse("Partenaire introuvable.");
        }

        return $this->successResponse($partner, "Partenaire chargé avec succès.");
    }

    public function update(PartnerRequest $request, string $id)
    {
        $partner = Partner::find($id);

        if (! $partner) {
            return $this->errorResponse("Partenaire introuvable.");
        }

        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        $logData = [
            'old_value' => $partner->toArray(),
            'new_value' => $data,
        ];

        $partner->update($data);

        logActivity("Modification d'un partenaire", $logData, $partner);

        return $this->successResponse($partner->fresh(), "Partenaire modifié avec succès.");
    }

    public function destroy(string $id)
    {
        $partner = Partner::find($id);

        if (! $partner) {
            return $this->errorResponse("Partenaire introuvable.");
        }

        logActivity("Suppression d'un partenaire", $partner->toArray(), $partner);

        $partner->delete();

        return $this->noContentSuccessResponse("Partenaire supprimé avec succès.");
    }
}