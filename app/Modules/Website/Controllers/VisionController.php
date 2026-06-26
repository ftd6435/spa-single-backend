<?php

namespace App\Modules\Website\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Website\Models\Vision;
use App\Modules\Website\Requests\VisionRequest;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion des visions du module Website.
 *
 * Ce contrôleur expose les opérations liées aux visions affichées sur le site :
 * - listing administratif complet des visions ;
 * - listing public limité pour le site vitrine ;
 * - création d’une vision ;
 * - affichage d’une vision spécifique ;
 * - modification d’une vision ;
 * - suppression d’une vision.
 *
 * Une vision représente généralement un contenu éditorial ou institutionnel
 * permettant de présenter la direction, les convictions ou la philosophie
 * de l’entreprise.
 *
 * Les réponses HTTP sont centralisées via le trait ApiResponses afin de garantir
 * une structure JSON homogène dans l’ensemble de l’API.
 *
 * Les champs d’audit `created_by` et `updated_by` sont automatiquement renseignés
 * à partir de l’utilisateur authentifié lors des actions administratives.
 *
 * Les actions sensibles sont journalisées avec logActivity() afin de conserver
 * une trace exploitable pour le suivi technique et l’audit applicatif.
 */
class VisionController extends Controller
{
    use ApiResponses;

    /**
     * Récupère la liste complète des visions pour l’administration.
     *
     * Chaque vision est retournée avec ses relations d’audit :
     * - `createdBy` : utilisateur ayant créé la vision ;
     * - `updatedBy` : utilisateur ayant effectué la dernière modification.
     *
     * Les résultats sont triés du plus récent au plus ancien afin de faciliter
     * leur consultation dans une interface d’administration.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Chargement des visions avec leurs informations d’audit.
        $visions = Vision::with('createdBy', 'updatedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        // Retour d’une réponse JSON standardisée contenant la liste des visions.
        return $this->successResponse($visions, 'Liste des visions chargée avec succès.');
    }

    /**
     * Récupère la liste publique des visions.
     *
     * Cette méthode est destinée à l’affichage côté site vitrine.
     * Elle limite volontairement les champs retournés afin d’exposer uniquement
     * les données utiles à l’interface publique.
     *
     * Les champs exposés publiquement sont :
     * - `id` : identifiant de la vision ;
     * - `title` : titre de la vision ;
     * - `description` : contenu descriptif ;
     * - `author` : auteur ou personne associée à la vision.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function publicIndex()
    {
        // Construction d’une requête publique avec sélection contrôlée des champs.
        $visions = Vision::query()
            ->select('id', 'title', 'description', 'author')
            ->orderBy('created_at', 'desc')
            ->get();

        // Retour d’une réponse JSON standardisée contenant la liste publique des visions.
        return $this->successResponse($visions, 'Liste des visions chargée avec succès.');
    }

    /**
     * Crée une nouvelle vision.
     *
     * Les données entrantes sont validées par VisionRequest avant d’être utilisées.
     * Le champ `created_by` est automatiquement renseigné avec l’identifiant
     * de l’utilisateur authentifié.
     *
     * Une journalisation est effectuée après la création afin de conserver
     * une trace de l’action réalisée.
     *
     * @param VisionRequest $request Requête contenant les données validées de la vision.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(VisionRequest $request)
    {
        // Récupération des données validées par la FormRequest.
        $data = $request->validated();

        // Association de la vision à l’utilisateur authentifié ayant réalisé la création.
        $data['created_by'] = Auth::id();

        // Création de la vision en base de données.
        $vision = Vision::create($data);

        // Journalisation de l’action de création pour la traçabilité applicative.
        logActivity("Création d'une vision", $data, $vision);

        // Retour de la vision créée avec ses relations d’audit chargées.
        return $this->successResponse(
            $vision->load('createdBy', 'updatedBy'),
            'Vision créée avec succès.'
        );
    }

    /**
     * Affiche les informations complètes d’une vision spécifique.
     *
     * La vision est recherchée à partir de son identifiant.
     * Les relations d’audit sont chargées afin d’identifier les utilisateurs
     * associés à la création et à la dernière modification.
     *
     * Si aucune vision correspondante n’est trouvée, une réponse d’erreur
     * standardisée est retournée.
     *
     * @param string $id Identifiant de la vision à récupérer.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        // Recherche de la vision avec ses informations d’audit.
        $vision = Vision::with('createdBy', 'updatedBy')->find($id);

        // Gestion du cas où la vision demandée n’existe pas.
        if (! $vision) {
            return $this->errorResponse('Vision introuvable.');
        }

        // Retour de la vision trouvée dans une réponse JSON standardisée.
        return $this->successResponse($vision, 'Vision chargée avec succès.');
    }

    /**
     * Met à jour les informations d’une vision existante.
     *
     * La vision est d’abord recherchée à partir de son identifiant.
     * Les nouvelles données sont ensuite validées par VisionRequest.
     *
     * Le champ `updated_by` est automatiquement renseigné avec l’identifiant
     * de l’utilisateur authentifié afin de conserver la traçabilité de la modification.
     *
     * Les anciennes valeurs et les nouvelles données sont préparées pour la
     * journalisation afin de conserver une trace claire de l’évolution de l’enregistrement.
     *
     * @param VisionRequest $request Requête contenant les données validées.
     * @param string $id Identifiant de la vision à modifier.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(VisionRequest $request, string $id)
    {
        // Recherche de la vision à modifier.
        $vision = Vision::find($id);

        // Gestion du cas où la vision demandée n’existe pas.
        if (! $vision) {
            return $this->errorResponse('Vision introuvable.');
        }

        // Récupération des données validées par la FormRequest.
        $data = $request->validated();

        // Association de la modification à l’utilisateur authentifié.
        $data['updated_by'] = Auth::id();

        // Préparation des données de journalisation avec l’état avant/après.
        $logData = [
            'old_value' => $vision->toArray(),
            'new_value' => $data,
        ];

        // Mise à jour de la vision en base de données.
        $vision->update($data);

        // Journalisation de l’action de modification.
        logActivity("Modification d'une vision", $logData, $vision);

        // Retour de la vision actualisée avec ses relations d’audit.
        return $this->successResponse(
            $vision->fresh()->load('createdBy', 'updatedBy'),
            'Vision modifiée avec succès.'
        );
    }

    /**
     * Supprime une vision existante.
     *
     * La vision est recherchée par son identifiant.
     * Avant suppression, ses données sont journalisées afin de conserver une trace
     * de l’élément supprimé.
     *
     * Si aucune vision correspondante n’est trouvée, une réponse d’erreur
     * standardisée est retournée.
     *
     * @param string $id Identifiant de la vision à supprimer.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        // Recherche de la vision à supprimer.
        $vision = Vision::find($id);

        // Gestion du cas où la vision demandée n’existe pas.
        if (! $vision) {
            return $this->errorResponse('Vision introuvable.');
        }

        // Journalisation avant suppression afin de conserver les données supprimées.
        logActivity("Suppression d'une vision", $vision->toArray(), $vision);

        // Suppression de la vision en base de données.
        $vision->delete();

        // Retour d’une réponse de succès sans contenu métier.
        return $this->noContentSuccessResponse('Vision supprimée avec succès.');
    }
}