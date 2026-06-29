<?php

namespace App\Modules\Website\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Website\Models\Testimonial;
use App\Modules\Website\Requests\TestimonialRequest;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion des témoignages du module Website.
 *
 * Ce contrôleur expose les opérations liées aux témoignages clients :
 * - listing administratif complet des témoignages ;
 * - listing public limité pour le site vitrine ;
 * - création d’un témoignage ;
 * - affichage d’un témoignage spécifique ;
 * - modification d’un témoignage ;
 * - suppression d’un témoignage.
 *
 * Un témoignage est associé à deux entités principales :
 * - un client, qui représente l’auteur ou la personne liée au témoignage ;
 * - un projet, qui représente le projet concerné par le témoignage.
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
class TestimonialController extends Controller
{
    use ApiResponses;

    /**
     * Récupère la liste complète des témoignages pour l’administration.
     *
     * Chaque témoignage est retourné avec ses relations principales :
     * - `client` : client associé au témoignage ;
     * - `project` : projet concerné par le témoignage ;
     * - `createdBy` : utilisateur ayant créé le témoignage ;
     * - `updatedBy` : utilisateur ayant effectué la dernière modification.
     *
     * Les résultats sont triés du plus récent au plus ancien afin de faciliter
     * leur consultation dans une interface d’administration.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Chargement des témoignages avec leurs relations métier et d’audit.
        $testimonials = Testimonial::with('client', 'project', 'createdBy', 'updatedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        // Retour d’une réponse JSON standardisée contenant la liste des témoignages.
        return $this->successResponse(
            $testimonials,
            'Liste des témoignages chargée avec succès.'
        );
    }

    /**
     * Récupère la liste publique des témoignages.
     *
     * Cette méthode est destinée à l’affichage côté site vitrine.
     * Elle limite volontairement les champs retournés afin d’exposer uniquement
     * les données nécessaires à l’interface publique.
     *
     * Pour chaque témoignage, seuls les champs publics suivants sont sélectionnés :
     * - l’identifiant du témoignage ;
     * - le contenu du témoignage ;
     * - les identifiants relationnels nécessaires au chargement des relations.
     *
     * Les relations `client` et `project` sont également limitées à certains champs
     * afin d’éviter d’exposer des informations inutiles ou internes côté public.
     *
     * Les champs `project_id` et `client_id` sont masqués après le chargement
     * des relations, car ils sont uniquement nécessaires à la construction
     * de la réponse et non à l’affichage final.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function publicIndex()
    {
        // Construction d’une requête publique avec sélection contrôlée des champs.
        $testimonials = Testimonial::query()
            ->select('id', 'project_id', 'client_id', 'content')
            ->with([
                // Chargement limité des informations publiques du client.
                'client:id,first_name,last_name,job_title',

                // Chargement limité des informations publiques du projet associé.
                'project:id,title,short_description',
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->each->makeHidden(['project_id', 'client_id']);

        // Retour d’une réponse JSON standardisée contenant la liste publique des témoignages.
        return $this->successResponse(
            $testimonials,
            'Liste des témoignages chargée avec succès.'
        );
    }

    /**
     * Crée un nouveau témoignage.
     *
     * Les données entrantes sont validées par TestimonialRequest avant d’être utilisées.
     * Le champ `created_by` est automatiquement renseigné avec l’identifiant
     * de l’utilisateur authentifié.
     *
     * Une journalisation est effectuée après la création afin de conserver
     * une trace de l’action réalisée.
     *
     * @param TestimonialRequest $request Requête contenant les données validées du témoignage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(TestimonialRequest $request)
    {
        // Récupération des données validées par la FormRequest.
        $data = $request->validated();

        // Association du témoignage à l’utilisateur authentifié ayant réalisé la création.
        $data['created_by'] = Auth::id();

        // Création du témoignage en base de données.
        $testimonial = Testimonial::create($data);

        // Journalisation de l’action de création pour la traçabilité applicative.
        logActivity("Création d'un témoignage", $data, $testimonial);

        // Retour du témoignage créé avec ses relations métier et d’audit.
        return $this->successResponse(
            $testimonial->load('client', 'project', 'createdBy', 'updatedBy'),
            'Témoignage créé avec succès.'
        );
    }

    /**
     * Affiche les informations complètes d’un témoignage spécifique.
     *
     * Le témoignage est recherché à partir de son identifiant.
     * Les relations métier et d’audit sont chargées afin de fournir une vue complète
     * en contexte administratif.
     *
     * Si aucun témoignage correspondant n’est trouvé, une réponse d’erreur
     * standardisée est retournée.
     *
     * @param string $id Identifiant du témoignage à récupérer.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        // Recherche du témoignage avec ses relations métier et d’audit.
        $testimonial = Testimonial::with('client', 'project', 'createdBy', 'updatedBy')
            ->find($id);

        // Gestion du cas où le témoignage demandé n’existe pas.
        if (! $testimonial) {
            return $this->errorResponse('Témoignage introuvable.');
        }

        // Retour du témoignage trouvé dans une réponse JSON standardisée.
        return $this->successResponse($testimonial, 'Témoignage chargé avec succès.');
    }

    /**
     * Met à jour les informations d’un témoignage existant.
     *
     * Le témoignage est d’abord recherché à partir de son identifiant.
     * Les nouvelles données sont ensuite validées par TestimonialRequest.
     *
     * Le champ `updated_by` est automatiquement renseigné avec l’identifiant
     * de l’utilisateur authentifié afin de conserver la traçabilité de la modification.
     *
     * Les anciennes valeurs et les nouvelles données sont préparées pour la
     * journalisation afin de conserver une trace claire de l’évolution de l’enregistrement.
     *
     * @param TestimonialRequest $request Requête contenant les données validées.
     * @param string $id Identifiant du témoignage à modifier.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(TestimonialRequest $request, string $id)
    {
        // Recherche du témoignage à modifier.
        $testimonial = Testimonial::find($id);

        // Gestion du cas où le témoignage demandé n’existe pas.
        if (! $testimonial) {
            return $this->errorResponse('Témoignage introuvable.');
        }

        // Récupération des données validées par la FormRequest.
        $data = $request->validated();

        // Association de la modification à l’utilisateur authentifié.
        $data['updated_by'] = Auth::id();

        // Préparation des données de journalisation avec l’état avant/après.
        $logData = [
            'old_value' => $testimonial->toArray(),
            'new_value' => $data,
        ];

        // Mise à jour du témoignage en base de données.
        $testimonial->update($data);

        // Journalisation de l’action de modification.
        logActivity("Modification d'un témoignage", $logData, $testimonial);

        // Retour du témoignage actualisé avec ses relations métier et d’audit.
        return $this->successResponse(
            $testimonial->fresh()->load('client', 'project', 'createdBy', 'updatedBy'),
            'Témoignage modifié avec succès.'
        );
    }

    /**
     * Supprime un témoignage existant.
     *
     * Le témoignage est recherché par son identifiant.
     * Avant suppression, ses données sont journalisées afin de conserver une trace
     * de l’élément supprimé.
     *
     * Si aucun témoignage correspondant n’est trouvé, une réponse d’erreur
     * standardisée est retournée.
     *
     * @param string $id Identifiant du témoignage à supprimer.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        // Recherche du témoignage à supprimer.
        $testimonial = Testimonial::find($id);

        // Gestion du cas où le témoignage demandé n’existe pas.
        if (! $testimonial) {
            return $this->errorResponse('Témoignage introuvable.');
        }

        // Journalisation avant suppression afin de conserver les données supprimées.
        logActivity("Suppression d'un témoignage", $testimonial->toArray(), $testimonial);

        // Suppression du témoignage en base de données.
        $testimonial->delete();

        // Retour d’une réponse de succès sans contenu métier.
        return $this->noContentSuccessResponse('Témoignage supprimé avec succès.');
    }
}