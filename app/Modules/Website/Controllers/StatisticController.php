<?php

namespace App\Modules\Website\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Website\Models\Statistic;
use App\Modules\Website\Requests\StatisticRequest;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion des statistiques du module Website.
 *
 * Ce contrôleur expose les opérations CRUD administratives liées aux statistiques :
 * - listing des statistiques ;
 * - création d’une statistique ;
 * - affichage d’une statistique spécifique ;
 * - modification d’une statistique ;
 * - suppression d’une statistique.
 *
 * Les statistiques correspondent généralement aux chiffres clés affichés sur le site
 * vitrine, par exemple un nombre de projets réalisés, de clients accompagnés,
 * d’années d’expérience ou toute autre donnée chiffrée valorisée côté public.
 *
 * Les réponses HTTP sont centralisées via le trait ApiResponses afin de garantir
 * une structure JSON homogène dans l’ensemble de l’API.
 *
 * Les champs d’audit `created_by` et `updated_by` sont automatiquement renseignés
 * à partir de l’utilisateur authentifié lors des actions de création et de mise à jour.
 *
 * Les actions sensibles sont journalisées avec logActivity() afin de conserver
 * une trace exploitable pour le suivi technique et l’audit applicatif.
 */
class StatisticController extends Controller
{
    use ApiResponses;

    /**
     * Récupère la liste complète des statistiques.
     *
     * Chaque statistique est retournée avec ses relations d’audit :
     * - `createdBy` : utilisateur ayant créé la statistique ;
     * - `updatedBy` : utilisateur ayant effectué la dernière modification.
     *
     * Les résultats sont triés du plus récent au plus ancien afin de faciliter
     * leur consultation dans une interface d’administration.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Chargement des statistiques avec leurs informations d’audit.
        $statistics = Statistic::with('createdBy', 'updatedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        // Retour d’une réponse JSON standardisée contenant la liste des statistiques.
        return $this->successResponse($statistics, 'Liste des statistiques chargée avec succès.');
    }

    /**
     * Crée une nouvelle statistique.
     *
     * Les données entrantes sont validées par StatisticRequest avant d’être utilisées.
     * Le champ `created_by` est automatiquement renseigné avec l’identifiant
     * de l’utilisateur authentifié.
     *
     * Une journalisation est effectuée après la création afin de conserver
     * une trace de l’action réalisée.
     *
     * @param StatisticRequest $request Requête contenant les données validées de la statistique.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StatisticRequest $request)
    {
        // Récupération des données validées par la FormRequest.
        $data = $request->validated();

        // Association de la statistique à l’utilisateur authentifié ayant réalisé la création.
        $data['created_by'] = Auth::id();

        // Création de la statistique en base de données.
        $statistic = Statistic::create($data);

        // Journalisation de l’action de création pour la traçabilité applicative.
        logActivity("Création d'une statistique", $data, $statistic);

        // Retour de la statistique créée avec ses relations d’audit chargées.
        return $this->successResponse(
            $statistic->load('createdBy', 'updatedBy'),
            'Statistique créée avec succès.'
        );
    }

    /**
     * Affiche les informations d’une statistique spécifique.
     *
     * La statistique est recherchée à partir de son identifiant.
     * Les relations d’audit sont chargées afin d’identifier les utilisateurs
     * associés à la création et à la dernière modification.
     *
     * Si aucune statistique correspondante n’est trouvée, une réponse d’erreur
     * standardisée est retournée.
     *
     * @param string $id Identifiant de la statistique à récupérer.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        // Recherche de la statistique avec ses informations d’audit.
        $statistic = Statistic::with('createdBy', 'updatedBy')->find($id);

        // Gestion du cas où la statistique demandée n’existe pas.
        if (! $statistic) {
            return $this->errorResponse('Statistique introuvable.');
        }

        // Retour de la statistique trouvée dans une réponse JSON standardisée.
        return $this->successResponse($statistic, 'Statistique chargée avec succès.');
    }

    /**
     * Met à jour les informations d’une statistique existante.
     *
     * La statistique est d’abord recherchée à partir de son identifiant.
     * Les nouvelles données sont ensuite validées par StatisticRequest.
     *
     * Le champ `updated_by` est automatiquement renseigné avec l’identifiant
     * de l’utilisateur authentifié afin de conserver la traçabilité de la modification.
     *
     * Les anciennes valeurs et les nouvelles données sont préparées pour la
     * journalisation afin de conserver une trace claire de l’évolution de l’enregistrement.
     *
     * @param StatisticRequest $request Requête contenant les données validées.
     * @param string $id Identifiant de la statistique à modifier.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(StatisticRequest $request, string $id)
    {
        // Recherche de la statistique à modifier.
        $statistic = Statistic::find($id);

        // Gestion du cas où la statistique demandée n’existe pas.
        if (! $statistic) {
            return $this->errorResponse('Statistique introuvable.');
        }

        // Récupération des données validées par la FormRequest.
        $data = $request->validated();

        // Association de la modification à l’utilisateur authentifié.
        $data['updated_by'] = Auth::id();

        // Préparation des données de journalisation avec l’état avant/après.
        $logData = [
            'old_value' => $statistic->toArray(),
            'new_value' => $data,
        ];

        // Mise à jour de la statistique en base de données.
        $statistic->update($data);

        // Journalisation de l’action de modification.
        logActivity("Modification d'une statistique", $logData, $statistic);

        // Retour de la statistique actualisée avec ses relations d’audit.
        return $this->successResponse(
            $statistic->fresh()->load('createdBy', 'updatedBy'),
            'Statistique modifiée avec succès.'
        );
    }

    /**
     * Supprime une statistique existante.
     *
     * La statistique est recherchée à partir de son identifiant.
     * Avant suppression, ses données sont journalisées afin de conserver
     * une trace de l’élément supprimé.
     *
     * Si aucune statistique correspondante n’est trouvée, une réponse d’erreur
     * standardisée est retournée.
     *
     * @param string $id Identifiant de la statistique à supprimer.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        // Recherche de la statistique à supprimer.
        $statistic = Statistic::find($id);

        // Gestion du cas où la statistique demandée n’existe pas.
        if (! $statistic) {
            return $this->errorResponse('Statistique introuvable.');
        }

        // Journalisation avant suppression afin de conserver les données supprimées.
        logActivity("Suppression d'une statistique", $statistic->toArray(), $statistic);

        // Suppression de la statistique en base de données.
        $statistic->delete();

        // Retour d’une réponse de succès sans contenu métier.
        return $this->noContentSuccessResponse('Statistique supprimée avec succès.');
    }
}