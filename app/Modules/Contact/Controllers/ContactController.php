<?php

namespace App\Modules\Contact\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Contact\Models\Contact;
use App\Modules\Contact\Requests\ContactRequest;
use App\Modules\Contact\Resources\ContactResource;
use App\Traits\ApiResponses;

// Gestion des messages du formulaire de contact (envoi public, consultation et suppression admin)
class ContactController extends Controller
{
    use ApiResponses;

    // Route admin — liste tous les messages reçus, du plus récent au plus ancien
    public function index()
    {
        $contacts = Contact::orderBy('created_at', 'desc')->get();

        return $this->successResponse(ContactResource::collection($contacts), "Liste des messages de contact chargée avec succès.");
    }

    // Route publique — tout visiteur peut envoyer un message via le formulaire de contact
    public function store(ContactRequest $request)
    {
        $data = $request->validated();

        $contact = Contact::create($data);

        logActivity("Réception d'un message de contact", $data, $contact);

        return $this->successResponse(new ContactResource($contact), "Votre message a bien été envoyé.");
    }

    // Route admin — détail d'un message de contact spécifique
    public function show(string $id)
    {
        $contact = Contact::find($id);

        if (! $contact) {
            return $this->errorResponse("Message de contact introuvable");
        }

        return $this->successResponse(new ContactResource($contact), "Message de contact chargé avec succès.");
    }

    // Route admin — suppression définitive d'un message de contact
    public function destroy(string $id)
    {
        $contact = Contact::find($id);

        if (! $contact) {
            return $this->errorResponse("Message de contact introuvable");
        }

        logActivity("Suppression d'un message de contact", $contact->toArray(), $contact);
        $contact->delete();

        return $this->noContentSuccessResponse("Message de contact supprimé avec succès");
    }
}
