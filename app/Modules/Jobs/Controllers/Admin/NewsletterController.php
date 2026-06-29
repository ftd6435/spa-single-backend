<?php

namespace App\Modules\Jobs\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Jobs\Models\Newsletter;
use App\Modules\Jobs\Resources\NewsletterResource;
use App\Traits\ApiResponses;

class NewsletterController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $newsletters = Newsletter::orderBy('created_at', 'desc')->get();

        return $this->successResponse(
            NewsletterResource::collection($newsletters),
            "Liste des abonnements newsletter chargée avec succès."
        );
    }

    public function show(string $id)
    {
        $newsletter = Newsletter::find($id);

        if (! $newsletter) {
            return $this->errorResponse("Abonnement newsletter introuvable.");
        }

        return $this->successResponse(
            new NewsletterResource($newsletter),
            "Abonnement newsletter chargé avec succès."
        );
    }

    public function switchStatus(string $id)
    {
        $newsletter = Newsletter::find($id);

        if (! $newsletter) {
            return $this->errorResponse("Abonnement newsletter introuvable.");
        }

        $oldValue = $newsletter->toArray();

        $newsletter->is_subscribed = ! $newsletter->is_subscribed;
        $newsletter->save();

        $logData = [
            'old_value' => $oldValue,
            'new_value' => $newsletter->toArray(),
        ];

        logActivity(
            "Changement du statut d'un abonnement newsletter",
            $logData,
            $newsletter
        );

        return $this->noContentSuccessResponse(
            "Statut de l'abonnement newsletter mis à jour avec succès."
        );
    }

    public function destroy(string $id)
    {
        $newsletter = Newsletter::find($id);

        if (! $newsletter) {
            return $this->errorResponse("Abonnement newsletter introuvable.");
        }

        logActivity(
            "Suppression d'un abonnement newsletter",
            $newsletter->toArray(),
            $newsletter
        );

        $newsletter->delete();

        return $this->noContentSuccessResponse(
            "Abonnement newsletter supprimé avec succès."
        );
    }
}