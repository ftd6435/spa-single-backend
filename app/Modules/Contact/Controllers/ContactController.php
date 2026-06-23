<?php

namespace App\Modules\Contact\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Contact\Models\Contact;
use App\Modules\Contact\Requests\ContactRequest;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $contacts = Contact::with('createdBy', 'updatedBy')->orderBy('created_at', 'desc')->get();

        return $this->successResponse($contacts, "Liste des messages de contact chargée avec succès.");
    }

    public function store(ContactRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $contact = Contact::create($data);

        logActivity("Réception d'un message de contact", $data, $contact);

        return $this->successResponse($contact, "Votre message a bien été envoyé.");
    }

    public function show(string $id)
    {
        $contact = Contact::with('createdBy', 'updatedBy')->find($id);

        if (! $contact) {
            return $this->errorResponse("Message de contact introuvable");
        }

        return $this->successResponse($contact, "Message de contact chargé avec succès");
    }

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