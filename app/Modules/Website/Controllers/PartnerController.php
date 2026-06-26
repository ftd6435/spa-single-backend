<?php

namespace App\Modules\Website\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Website\Models\Partner;
use App\Modules\Website\Requests\PartnerRequest;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de gestion des partenaires du module Website.
 *
 * Ce contrôleur expose les opérations CRUD administratives liées aux partenaires :
 * - affichage de la liste des partenaires ;
 * - création d’un partenaire avec gestion éventuelle du logo ;
 * - affichage d’un partenaire spécifique ;
 * - modification d’un partenaire avec remplacement éventuel du logo ;
 * - suppression d’un partenaire avec suppression éventuelle du logo associé.
 *
 * Les réponses HTTP sont standardisées grâce au trait ApiResponses.
 *
 * Le stockage et la suppression des images sont délégués au trait CloudflareUpload,
 * ce qui permet de centraliser la logique d’upload externe.
 *
 * Les champs d’audit `created_by` et `updated_by` sont automatiquement renseignés
 * à partir de l’utilisateur authentifié.
 */
class PartnerController extends Controller
{
    use ApiResponses, CloudflareUpload;

    /**
     * Récupère la liste complète des partenaires.
     *
     * Les partenaires sont retournés avec leurs relations d’audit :
     * - `createdBy` : utilisateur ayant créé le partenaire ;
     * - `updatedBy` : utilisateur ayant effectué la dernière modification.
     *
     * Les résultats sont triés du plus récent au plus ancien afin de faciliter
     * l’affichage dans une interface d’administration.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Chargement des partenaires avec les relations d’audit.
        $partners = Partner::with('createdBy', 'updatedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        // Retour d’une réponse JSON standardisée contenant la liste des partenaires.
        return $this->successResponse($partners, "Liste des partenaires chargée avec succès.");
    }

    /**
     * Crée un nouveau partenaire.
     *
     * Les données entrantes sont validées par PartnerRequest avant traitement.
     * Si un fichier logo est fourni, il est uploadé dans l’espace dédié aux partenaires.
     *
     * En cas d’erreur après l’upload du logo, le fichier nouvellement envoyé est supprimé
     * afin d’éviter de conserver un fichier orphelin dans le stockage externe.
     *
     * Le champ `created_by` est automatiquement renseigné avec l’identifiant
     * de l’utilisateur authentifié.
     *
     * @param PartnerRequest $request Requête contenant les données validées du partenaire.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function store(PartnerRequest $request)
    {
        // Récupération des données validées par la FormRequest.
        $data = $request->validated();

        // Variable utilisée pour suivre le logo uploadé et permettre un rollback en cas d’erreur.
        $uploadedLogo = null;

        try {
            // Vérifie si un logo a été envoyé dans la requête.
            if ($request->hasFile('logo')) {
                // Upload du logo dans le dossier logique des partenaires.
                $uploadedLogo = $this->uploadImage($request->file('logo'), 'partners');

                // Enregistrement du chemin retourné par le service d’upload.
                $data['logo_path'] = $uploadedLogo;
            }

            // Suppression de la clé `logo` des données, car seul `logo_path` doit être persisté.
            unset($data['logo']);

            // Association du partenaire à l’utilisateur authentifié ayant réalisé la création.
            $data['created_by'] = Auth::id();

            // Création du partenaire en base de données.
            $partner = Partner::create($data);

            // Journalisation de l’action de création pour la traçabilité.
            logActivity("Création d'un partenaire", $data, $partner);

            // Retour d’une réponse JSON standardisée avec le partenaire créé.
            return $this->successResponse($partner, "Partenaire créé avec succès.");
        } catch (\Throwable $e) {
            // En cas d’échec après upload, suppression du logo nouvellement envoyé.
            if ($uploadedLogo) {
                $this->deleteImage($uploadedLogo, 'partners');
            }

            // Relance de l’exception pour laisser Laravel gérer l’erreur selon la configuration globale.
            throw $e;
        }
    }

    /**
     * Affiche les informations d’un partenaire spécifique.
     *
     * Le partenaire est recherché à partir de son identifiant.
     * Si aucun partenaire correspondant n’est trouvé, une réponse d’erreur est retournée.
     *
     * @param string $id Identifiant du partenaire à récupérer.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        // Recherche du partenaire avec ses relations d’audit.
        $partner = Partner::with('createdBy', 'updatedBy')->find($id);

        // Gestion du cas où le partenaire demandé n’existe pas.
        if (! $partner) {
            return $this->errorResponse("Partenaire introuvable.");
        }

        // Retour du partenaire trouvé dans une réponse JSON standardisée.
        return $this->successResponse($partner, "Partenaire chargé avec succès.");
    }

    /**
     * Met à jour les informations d’un partenaire existant.
     *
     * Le partenaire est d’abord recherché par son identifiant.
     * Les nouvelles données sont ensuite validées par PartnerRequest.
     *
     * Si un nouveau logo est fourni :
     * - le nouveau logo est uploadé ;
     * - le chemin du nouveau logo est enregistré ;
     * - l’ancien logo est supprimé uniquement après la mise à jour réussie.
     *
     * En cas d’erreur après l’upload du nouveau logo, celui-ci est supprimé afin
     * d’éviter de conserver un fichier non utilisé.
     *
     * Le champ `updated_by` est automatiquement renseigné avec l’identifiant
     * de l’utilisateur authentifié.
     *
     * @param PartnerRequest $request Requête contenant les données validées.
     * @param string $id Identifiant du partenaire à modifier.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function update(PartnerRequest $request, string $id)
    {
        // Recherche du partenaire à modifier.
        $partner = Partner::find($id);

        // Gestion du cas où le partenaire n’existe pas.
        if (! $partner) {
            return $this->errorResponse("Partenaire introuvable.");
        }

        // Récupération des données validées par la FormRequest.
        $data = $request->validated();

        // Sauvegarde du chemin de l’ancien logo pour suppression éventuelle après mise à jour.
        $oldLogo = $partner->logo_path;

        // Variable utilisée pour suivre le nouveau logo uploadé en cas d’erreur.
        $newLogo = null;

        try {
            // Vérifie si un nouveau logo a été envoyé dans la requête.
            if ($request->hasFile('logo')) {
                // Upload du nouveau logo dans le dossier logique des partenaires.
                $newLogo = $this->uploadImage($request->file('logo'), 'partners');

                // Enregistrement du chemin du nouveau logo dans les données à persister.
                $data['logo_path'] = $newLogo;
            }

            // Suppression de la clé `logo`, car le modèle doit stocker uniquement `logo_path`.
            unset($data['logo']);

            // Association de la modification à l’utilisateur authentifié.
            $data['updated_by'] = Auth::id();

            // Préparation des données de journalisation avec l’état avant/après.
            $logData = [
                'old_value' => $partner->toArray(),
                'new_value' => $data,
            ];

            // Mise à jour du partenaire en base de données.
            $partner->update($data);

            // Suppression de l’ancien logo uniquement si un nouveau logo a été uploadé avec succès.
            if ($newLogo && $oldLogo) {
                $this->deleteImage($oldLogo, 'partners');
            }

            // Journalisation de l’action de modification.
            logActivity("Modification d'un partenaire", $logData, $partner);

            // Retour du partenaire actualisé dans une réponse JSON standardisée.
            return $this->successResponse($partner->fresh(), "Partenaire modifié avec succès.");
        } catch (\Throwable $e) {
            // En cas d’échec après upload du nouveau logo, suppression du fichier nouvellement envoyé.
            if ($newLogo) {
                $this->deleteImage($newLogo, 'partners');
            }

            // Relance de l’exception pour conserver le comportement d’erreur global de l’application.
            throw $e;
        }
    }

    /**
     * Supprime un partenaire existant.
     *
     * Le partenaire est recherché par son identifiant.
     * Avant suppression, les données du partenaire sont journalisées afin de conserver
     * une trace de l’élément supprimé.
     *
     * Si le partenaire possède un logo, celui-ci est supprimé du stockage externe
     * après la suppression de l’enregistrement en base de données.
     *
     * @param string $id Identifiant du partenaire à supprimer.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        // Recherche du partenaire à supprimer.
        $partner = Partner::find($id);

        // Gestion du cas où le partenaire demandé n’existe pas.
        if (! $partner) {
            return $this->errorResponse("Partenaire introuvable.");
        }

        // Conservation du chemin du logo avant suppression du modèle.
        $logo = $partner->logo_path;

        // Journalisation avant suppression afin de conserver les données supprimées.
        logActivity("Suppression d'un partenaire", $partner->toArray(), $partner);

        // Suppression du partenaire en base de données.
        $partner->delete();

        // Suppression du logo associé si celui-ci existe.
        if ($logo) {
            $this->deleteImage($logo, 'partners');
        }

        // Retour d’une réponse de succès sans contenu métier.
        return $this->noContentSuccessResponse("Partenaire supprimé avec succès.");
    }
}