<?php

namespace App\Modules\Website\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Website\Models\Project;
use App\Modules\Website\Requests\ProjectRequest;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion des projets du module Website.
 *
 * Ce contrôleur expose deux types d’accès aux projets :
 * - un accès administratif complet, incluant les champs d’audit ;
 * - un accès public limité, destiné à l’affichage côté site vitrine.
 *
 * Les opérations administratives permettent :
 * - de lister tous les projets ;
 * - de créer un projet ;
 * - d’afficher un projet précis ;
 * - de modifier un projet ;
 * - de supprimer un projet.
 *
 * Les opérations publiques permettent :
 * - de lister les projets visibles avec filtres optionnels ;
 * - d’afficher le détail public d’un projet.
 *
 * Les réponses JSON sont centralisées via le trait ApiResponses afin de conserver
 * une structure homogène sur l’ensemble de l’API.
 *
 * Les champs d’audit `created_by` et `updated_by` sont automatiquement renseignés
 * à partir de l’utilisateur authentifié pour les actions administratives.
 */
class ProjectController extends Controller
{
    use ApiResponses;

    /**
     * Récupère la liste complète des projets pour l’administration.
     *
     * Chaque projet est chargé avec ses relations principales :
     * - `category` : catégorie associée au projet ;
     * - `service` : service associé au projet ;
     * - `createdBy` : utilisateur ayant créé le projet ;
     * - `updatedBy` : utilisateur ayant effectué la dernière modification.
     *
     * Les projets sont triés du plus récent au plus ancien afin de faciliter
     * leur consultation dans un back-office.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Chargement des projets avec leurs relations métier et d’audit.
        $projects = Project::with('category', 'service', 'createdBy', 'updatedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        // Retour d’une réponse JSON standardisée contenant la liste des projets.
        return $this->successResponse($projects, 'Liste des projets chargée avec succès.');
    }

    /**
     * Récupère la liste publique des projets.
     *
     * Cette méthode est destinée à l’affichage côté site public.
     * Elle limite volontairement les champs retournés afin d’exposer uniquement
     * les données nécessaires à la vitrine.
     *
     * Deux filtres optionnels peuvent être appliqués :
     * - `category_id` : filtrage par catégorie ;
     * - `service_id` : filtrage par service.
     *
     * Les relations `category` et `service` sont également limitées à certains champs
     * afin d’éviter d’exposer des données inutiles côté public.
     *
     * @param Request $request Requête HTTP contenant éventuellement les filtres publics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function publicIndex(Request $request)
    {
        // Validation des filtres publics optionnels.
        $filters = $request->validate([
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'service_id' => ['sometimes', 'integer', 'exists:services,id'],
        ]);

        // Construction de la requête publique avec sélection contrôlée des champs.
        $projects = Project::query()
            ->select(
                'id',
                'category_id',
                'service_id',
                'title',
                'short_description',
                'description',
                'demo_link'
            )
            ->with([
                // Chargement limité des informations publiques de la catégorie.
                'category:id,libelle,description,status',

                // Chargement limité des informations publiques du service associé.
                'service:id,icon,image_path,title,short_description,description,benefits',
            ])
            ->when(
                isset($filters['category_id']),
                fn ($query) => $query->where('category_id', $filters['category_id'])
            )
            ->when(
                isset($filters['service_id']),
                fn ($query) => $query->where('service_id', $filters['service_id'])
            )
            ->orderBy('created_at', 'desc')
            ->get();

        // Retour de la liste publique des projets dans une réponse JSON standardisée.
        return $this->successResponse($projects, 'Liste des projets chargée avec succès.');
    }

    /**
     * Crée un nouveau projet.
     *
     * Les données entrantes sont validées par ProjectRequest avant d’être utilisées.
     * Le champ `created_by` est automatiquement renseigné avec l’identifiant
     * de l’utilisateur authentifié.
     *
     * Une journalisation est effectuée après la création afin de conserver
     * une trace de l’action.
     *
     * @param ProjectRequest $request Requête contenant les données validées du projet.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProjectRequest $request)
    {
        // Récupération des données validées par la FormRequest.
        $data = $request->validated();

        // Association du projet à l’utilisateur authentifié ayant réalisé la création.
        $data['created_by'] = Auth::id();

        // Création du projet en base de données.
        $project = Project::create($data);

        // Journalisation de l’action de création pour la traçabilité applicative.
        logActivity("Création d'un projet", $data, $project);

        // Retour du projet créé avec ses relations métier et d’audit.
        return $this->successResponse(
            $project->load('category', 'service', 'createdBy', 'updatedBy'),
            'Projet créé avec succès.'
        );
    }

    /**
     * Affiche les informations complètes d’un projet pour l’administration.
     *
     * Le projet est recherché à partir de son identifiant.
     * Les relations métier et d’audit sont chargées afin de fournir une vue complète
     * en contexte administratif.
     *
     * Si aucun projet correspondant n’est trouvé, une réponse d’erreur est retournée.
     *
     * @param string $id Identifiant du projet à récupérer.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        // Recherche du projet avec ses relations métier et d’audit.
        $project = Project::with('category', 'service', 'createdBy', 'updatedBy')->find($id);

        // Gestion du cas où le projet demandé n’existe pas.
        if (! $project) {
            return $this->errorResponse('Projet introuvable.');
        }

        // Retour du projet trouvé dans une réponse JSON standardisée.
        return $this->successResponse($project, 'Projet chargé avec succès.');
    }

    /**
     * Affiche les informations publiques d’un projet spécifique.
     *
     * Cette méthode est destinée au site vitrine.
     * Elle limite les champs retournés pour le projet ainsi que pour ses relations
     * afin d’exposer uniquement les données utiles à l’affichage public.
     *
     * Si aucun projet correspondant n’est trouvé, une réponse d’erreur est retournée.
     *
     * @param string $id Identifiant du projet à récupérer.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function publicShow(string $id)
    {
        // Construction d’une requête publique avec sélection contrôlée des champs.
        $project = Project::query()
            ->select(
                'id',
                'category_id',
                'service_id',
                'title',
                'short_description',
                'description',
                'demo_link'
            )
            ->with([
                // Chargement limité des informations publiques de la catégorie.
                'category:id,libelle,description,status',

                // Chargement limité des informations publiques du service associé.
                'service:id,icon,image_path,title,short_description,description,benefits',
            ])
            ->find($id);

        // Gestion du cas où le projet demandé n’existe pas.
        if (! $project) {
            return $this->errorResponse('Projet introuvable.');
        }

        // Retour du projet public trouvé dans une réponse JSON standardisée.
        return $this->successResponse($project, 'Projet chargé avec succès.');
    }

    /**
     * Met à jour les informations d’un projet existant.
     *
     * Le projet est d’abord recherché à partir de son identifiant.
     * Les nouvelles données sont ensuite validées par ProjectRequest.
     *
     * Le champ `updated_by` est automatiquement renseigné avec l’identifiant
     * de l’utilisateur authentifié.
     *
     * Les anciennes valeurs et les nouvelles données sont préparées pour la
     * journalisation afin de conserver une trace claire de la modification.
     *
     * @param ProjectRequest $request Requête contenant les données validées.
     * @param string $id Identifiant du projet à modifier.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ProjectRequest $request, string $id)
    {
        // Recherche du projet à modifier.
        $project = Project::find($id);

        // Gestion du cas où le projet n’existe pas.
        if (! $project) {
            return $this->errorResponse('Projet introuvable.');
        }

        // Récupération des données validées par la FormRequest.
        $data = $request->validated();

        // Association de la modification à l’utilisateur authentifié.
        $data['updated_by'] = Auth::id();

        // Préparation des données de journalisation avec l’état avant/après.
        $logData = [
            'old_value' => $project->toArray(),
            'new_value' => $data,
        ];

        // Mise à jour du projet en base de données.
        $project->update($data);

        // Journalisation de l’action de modification.
        logActivity("Modification d'un projet", $logData, $project);

        // Retour du projet actualisé avec ses relations métier et d’audit.
        return $this->successResponse(
            $project->fresh()->load('category', 'service', 'createdBy', 'updatedBy'),
            'Projet modifié avec succès.'
        );
    }

    /**
     * Supprime un projet existant.
     *
     * Le projet est recherché par son identifiant.
     * Avant suppression, ses données sont journalisées afin de conserver une trace
     * de l’élément supprimé.
     *
     * Si aucun projet correspondant n’est trouvé, une réponse d’erreur est retournée.
     *
     * @param string $id Identifiant du projet à supprimer.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        // Recherche du projet à supprimer.
        $project = Project::find($id);

        // Gestion du cas où le projet demandé n’existe pas.
        if (! $project) {
            return $this->errorResponse('Projet introuvable.');
        }

        // Journalisation avant suppression afin de conserver les données supprimées.
        logActivity("Suppression d'un projet", $project->toArray(), $project);

        // Suppression du projet en base de données.
        $project->delete();

        // Retour d’une réponse de succès sans contenu métier.
        return $this->noContentSuccessResponse('Projet supprimé avec succès.');
    }
}