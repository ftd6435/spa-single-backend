<?php

namespace App\Modules\Website\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Website\Models\Client;
use App\Modules\Website\Requests\ClientRequest;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion des clients du module Website.
 *
 * Ce contrôleur expose les opérations CRUD administratives liées aux clients :
 * - listing des clients ;
 * - création d’un client ;
 * - affichage d’un client spécifique ;
 * - modification d’un client ;
 * - suppression d’un client.
 *
 * Les réponses HTTP sont centralisées via le trait ApiResponses afin de garantir
 * une structure JSON homogène dans toute l’API.
 *
 * Les champs d’audit `created_by` et `updated_by` sont automatiquement renseignés
 * à partir de l’utilisateur authentifié.
 */
class ClientController extends Controller
{
    use ApiResponses;

    /**
     * Récupère la liste complète des clients.
     *
     * Les clients sont retournés avec les relations d’audit :
     * - `createdBy` : utilisateur ayant créé le client ;
     * - `updatedBy` : utilisateur ayant effectué la dernière modification.
     *
     * Le tri est effectué du plus récent au plus ancien afin de faciliter
     * l’affichage en back-office.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Chargement des clients avec leurs informations d’audit.
        $clients = Client::with('createdBy', 'updatedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        // Retour d’une réponse JSON standardisée contenant la liste des clients.
        return $this->successResponse($clients, 'Liste des clients chargée avec succès.');
    }

    /**
     * Crée un nouveau client.
     *
     * Les données entrantes sont validées par ClientRequest avant d’être utilisées.
     * Le champ `created_by` est automatiquement associé à l’utilisateur connecté.
     *
     * Une entrée de journalisation est créée après l’insertion afin de tracer
     * l’action de création.
     *
     * @param ClientRequest $request Requête contenant les données validées du client.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ClientRequest $request)
    {
        // Récupération des données validées par la FormRequest.
        $data = $request->validated();

        // Association du client à l’utilisateur authentifié ayant réalisé la création.
        $data['created_by'] = Auth::id();

        // Création du client en base de données.
        $client = Client::create($data);

        // Journalisation de l’action de création pour l’audit applicatif.
        logActivity("Création d'un client", $data, $client);

        // Retour du client créé avec ses relations d’audit chargées.
        return $this->successResponse(
            $client->load('createdBy', 'updatedBy'),
            'Client créé avec succès.'
        );
    }

    /**
     * Affiche les informations d’un client spécifique.
     *
     * Le client est recherché à partir de son identifiant.
     * Si aucun client correspondant n’est trouvé, une réponse d’erreur est retournée.
     *
     * @param string $id Identifiant du client à récupérer.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        // Recherche du client avec ses informations d’audit.
        $client = Client::with('createdBy', 'updatedBy')->find($id);

        // Gestion du cas où le client demandé n’existe pas.
        if (! $client) {
            return $this->errorResponse('Client introuvable.');
        }

        // Retour du client trouvé dans une réponse JSON standardisée.
        return $this->successResponse($client, 'Client chargé avec succès.');
    }

    /**
     * Met à jour les informations d’un client existant.
     *
     * Le client est d’abord recherché par son identifiant.
     * Les nouvelles données sont ensuite validées par ClientRequest.
     *
     * Le champ `updated_by` est renseigné avec l’identifiant de l’utilisateur
     * authentifié afin de conserver la traçabilité de la modification.
     *
     * Avant la mise à jour, les anciennes valeurs du client sont conservées
     * dans les données de journalisation.
     *
     * @param ClientRequest $request Requête contenant les données validées.
     * @param string $id Identifiant du client à modifier.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ClientRequest $request, string $id)
    {
        // Recherche du client à modifier.
        $client = Client::find($id);

        // Gestion du cas où le client n’existe pas.
        if (! $client) {
            return $this->errorResponse('Client introuvable.');
        }

        // Récupération des données validées par la FormRequest.
        $data = $request->validated();

        // Association de la modification à l’utilisateur authentifié.
        $data['updated_by'] = Auth::id();

        // Préparation des données de journalisation avec l’état avant/après.
        $logData = [
            'old_value' => $client->toArray(),
            'new_value' => $data,
        ];

        // Mise à jour du client en base de données.
        $client->update($data);

        // Journalisation de l’action de modification.
        logActivity("Modification d'un client", $logData, $client);

        // Retour du client actualisé avec ses relations d’audit.
        return $this->successResponse(
            $client->fresh()->load('createdBy', 'updatedBy'),
            'Client modifié avec succès.'
        );
    }

    /**
     * Supprime un client existant.
     *
     * Le client est recherché par son identifiant.
     * Si le client existe, une journalisation est effectuée avant la suppression
     * afin de conserver une trace de l’état supprimé.
     *
     * La suppression peut entraîner la suppression automatique des données liées
     * si des contraintes de cascade sont définies au niveau des migrations.
     *
     * @param string $id Identifiant du client à supprimer.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        // Recherche du client à supprimer.
        $client = Client::find($id);

        // Gestion du cas où le client demandé n’existe pas.
        if (! $client) {
            return $this->errorResponse('Client introuvable.');
        }

        // Journalisation avant suppression afin de conserver les données supprimées.
        logActivity("Suppression d'un client", $client->toArray(), $client);

        // Suppression du client en base de données.
        $client->delete();

        // Retour d’une réponse de succès sans contenu métier.
        return $this->noContentSuccessResponse('Client supprimé avec succès.');
    }
}