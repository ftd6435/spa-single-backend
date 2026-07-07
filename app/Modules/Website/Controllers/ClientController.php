<?php

namespace App\Modules\Website\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Website\Models\Client;
use App\Modules\Website\Requests\ClientRequest;
use App\Modules\Website\Resources\ClientResource;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $clients = Client::with('createdBy', 'updatedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            ClientResource::collection($clients),
            'Liste des clients chargée avec succès.'
        );
    }

    public function store(ClientRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $client = Client::create($data);

        logActivity("Création d'un client", $data, $client);

        return $this->successResponse(
            ClientResource::make($client->load('createdBy', 'updatedBy')),
            'Client créé avec succès.'
        );
    }

    public function show(string $id)
    {
        $client = Client::with('createdBy', 'updatedBy')->find($id);

        if (! $client) {
            return $this->errorResponse('Client introuvable.');
        }

        return $this->successResponse(
            ClientResource::make($client),
            'Client chargé avec succès.'
        );
    }

    public function update(ClientRequest $request, string $id)
    {
        $client = Client::find($id);

        if (! $client) {
            return $this->errorResponse('Client introuvable.');
        }

        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        $logData = [
            'old_value' => $client->toArray(),
            'new_value' => $data,
        ];

        $client->update($data);

        logActivity("Modification d'un client", $logData, $client);

        return $this->successResponse(
            ClientResource::make($client->fresh()->load('createdBy', 'updatedBy')),
            'Client modifié avec succès.'
        );
    }

    public function destroy(string $id)
    {
        $client = Client::find($id);

        if (! $client) {
            return $this->errorResponse('Client introuvable.');
        }

        logActivity("Suppression d'un client", $client->toArray(), $client);

        $client->delete();

        return $this->noContentSuccessResponse('Client supprimé avec succès.');
    }
}
