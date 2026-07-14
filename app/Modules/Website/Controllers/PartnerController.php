<?php

namespace App\Modules\Website\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Website\Models\Partner;
use App\Modules\Website\Requests\PartnerRequest;
use App\Modules\Website\Resources\PartnerResource;
use App\Modules\Website\Resources\PublicPartnerResource;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Illuminate\Support\Facades\Auth;

class PartnerController extends Controller
{
    use ApiResponses, CloudflareUpload;

    public function index()
    {
        $partners = Partner::with('createdBy', 'updatedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            PartnerResource::collection($partners),
            "Liste des partenaires chargée avec succès."
        );
    }

    public function publicIndex()
    {
        $partners = Partner::where('status', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            PublicPartnerResource::collection($partners),
            "Liste des partenaires chargée avec succès."
        );
    }

    public function store(PartnerRequest $request)
    {
        $data = $request->validated();
        $uploadedLogo = null;

        try {
            if ($request->hasFile('logo')) {
                $uploadedLogo = $this->uploadImage($request->file('logo'), 'partners');
                $data['logo_path'] = $uploadedLogo;
            }

            unset($data['logo']);

            $data['created_by'] = Auth::id();
            $partner = Partner::create($data);

            logActivity("Création d'un partenaire", $data, $partner);

            return $this->successResponse(
                PartnerResource::make($partner->load('createdBy', 'updatedBy')),
                "Partenaire créé avec succès."
            );
        } catch (\Throwable $e) {
            // Supprime le nouveau fichier si la création échoue après upload.
            if ($uploadedLogo) {
                $this->deleteImage($uploadedLogo, 'partners');
            }

            throw $e;
        }
    }

    public function show(string $id)
    {
        $partner = Partner::with('createdBy', 'updatedBy')->find($id);

        if (! $partner) {
            return $this->errorResponse("Partenaire introuvable.");
        }

        return $this->successResponse(
            PartnerResource::make($partner),
            "Partenaire chargé avec succès."
        );
    }

    public function publicShow(string $id)
    {
        $partner = Partner::where('status', true)->find($id);

        if (! $partner) {
            return $this->errorResponse("Partenaire introuvable.");
        }

        return $this->successResponse(
            PublicPartnerResource::make($partner),
            "Partenaire chargé avec succès."
        );
    }

    public function switchStatus(string $id)
    {
        $partner = Partner::find($id);

        if (! $partner) {
            return $this->errorResponse("Partenaire introuvable.");
        }

        $oldStatus = $partner->status;
        $partner->status = ! $oldStatus;
        $partner->updated_by = Auth::id();
        $partner->save();

        logActivity("Changement du statut d'un partenaire", [
            'old_value' => ['status' => $oldStatus],
            'new_value' => ['status' => $partner->status],
        ], $partner);

        return $this->noContentSuccessResponse("Statut du partenaire mis à jour avec succès.");
    }

    public function update(PartnerRequest $request, string $id)
    {
        $partner = Partner::find($id);

        if (! $partner) {
            return $this->errorResponse("Partenaire introuvable.");
        }

        $data = $request->validated();
        $oldLogo = $partner->logo_path;
        $newLogo = null;

        try {
            if ($request->hasFile('logo')) {
                $newLogo = $this->uploadImage($request->file('logo'), 'partners');
                $data['logo_path'] = $newLogo;
            }

            unset($data['logo']);

            $data['updated_by'] = Auth::id();

            $logData = [
                'old_value' => $partner->toArray(),
                'new_value' => $data,
            ];

            $partner->update($data);

            // Supprime l'ancien logo seulement après une mise à jour réussie.
            if ($newLogo && $oldLogo) {
                $this->deleteImage($oldLogo, 'partners');
            }

            logActivity("Modification d'un partenaire", $logData, $partner);

            return $this->successResponse(
                PartnerResource::make($partner->fresh()->load('createdBy', 'updatedBy')),
                "Partenaire modifié avec succès."
            );
        } catch (\Throwable $e) {
            // Supprime le nouveau fichier si la mise à jour échoue.
            if ($newLogo) {
                $this->deleteImage($newLogo, 'partners');
            }

            throw $e;
        }
    }

    public function destroy(string $id)
    {
        $partner = Partner::find($id);

        if (! $partner) {
            return $this->errorResponse("Partenaire introuvable.");
        }

        $logo = $partner->logo_path;

        logActivity("Suppression d'un partenaire", $partner->toArray(), $partner);

        $partner->delete();

        if ($logo) {
            $this->deleteImage($logo, 'partners');
        }

        return $this->noContentSuccessResponse("Partenaire supprimé avec succès.");
    }
}
