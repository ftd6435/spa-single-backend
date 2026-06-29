<?php

namespace App\Modules\Website\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Website\Models\Service;
use App\Modules\Website\Requests\ServiceRequest;
use App\Traits\ApiResponses;
use App\Traits\CloudflareUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur de gestion des services du module Website.
 *
 * Ce contrôleur expose les opérations CRUD administratives liées aux services :
 * - listing des services ;
 * - création d’un service avec image éventuelle ;
 * - affichage d’un service spécifique ;
 * - modification d’un service avec image et tags éventuels ;
 * - suppression d’un service avec suppression éventuelle de l’image associée.
 *
 * Les services peuvent être associés à plusieurs tags via une relation many-to-many.
 * La synchronisation de cette relation est gérée directement depuis ce contrôleur,
 * à partir du tableau `tag_ids` validé dans ServiceRequest.
 *
 * Les réponses HTTP sont centralisées via le trait ApiResponses afin de conserver
 * une structure JSON homogène dans l’API.
 *
 * La gestion des images est déléguée au trait CloudflareUpload, qui centralise
 * l’upload et la suppression des fichiers dans le stockage externe.
 *
 * Les opérations sensibles sont journalisées avec logActivity() afin de conserver
 * une trace des créations, modifications, suppressions et remplacements d’image.
 */
class ServiceController extends Controller
{
    use ApiResponses, CloudflareUpload;

    /**
     * Récupère la liste complète des services.
     *
     * Chaque service est chargé avec :
     * - ses tags associés ;
     * - l’utilisateur ayant créé l’enregistrement ;
     * - l’utilisateur ayant effectué la dernière modification.
     *
     * Les résultats sont triés du plus récent au plus ancien pour faciliter
     * leur consultation dans une interface d’administration.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Chargement des services avec leurs tags et leurs relations d’audit.
        $services = Service::with('tags', 'createdBy', 'updatedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        // Retour d’une réponse JSON standardisée contenant la liste des services.
        return $this->successResponse($services, 'Liste des services chargée avec succès.');
    }

    /**
     * Crée un nouveau service.
     *
     * Les données entrantes sont validées par ServiceRequest.
     * Les identifiants des tags sont normalisés afin de supprimer les doublons.
     *
     * Si une image est fournie, elle est uploadée avant la création du service.
     * En cas d’erreur après l’upload, l’image nouvellement envoyée est supprimée
     * pour éviter de conserver un fichier orphelin.
     *
     * La création du service et la synchronisation des tags sont exécutées dans
     * une transaction afin de garantir la cohérence entre la table `services`
     * et la table pivot associée aux tags.
     *
     * @param ServiceRequest $request Requête contenant les données validées du service.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function store(ServiceRequest $request)
    {
        // Récupération des données validées par la FormRequest.
        $data = $request->validated();

        // Normalisation du tableau des tags : valeurs uniques et réindexation du tableau.
        $tagIds = array_values(array_unique($data['tag_ids'] ?? []));

        // Variable utilisée pour suivre l’image uploadée et permettre un rollback fichier en cas d’erreur.
        $uploadedImage = null;

        // Suppression de `tag_ids` des données principales, car les tags sont gérés via la relation pivot.
        unset($data['tag_ids']);

        try {
            // Vérifie si une image a été envoyée dans la requête.
            if ($request->hasFile('image')) {
                // Upload de l’image dans le dossier logique des services.
                $uploadedImage = $this->uploadImage($request->file('image'), 'services');

                // Enregistrement du chemin retourné par le service d’upload.
                $data['image_path'] = $uploadedImage;
            }

            // Suppression de la clé `image`, car seul `image_path` doit être persisté en base.
            unset($data['image']);

            // Association du service à l’utilisateur authentifié ayant réalisé la création.
            $data['created_by'] = Auth::id();

            // Transaction garantissant que la création du service et l’association des tags restent cohérentes.
            $service = DB::transaction(function () use ($data, $tagIds) {
                // Création du service en base de données.
                $service = Service::create($data);

                // Synchronisation des tags uniquement si des tags ont été transmis.
                if (! empty($tagIds)) {
                    // Préparation des données pivot avec l’utilisateur créateur de chaque association service/tag.
                    $pivotData = collect($tagIds)
                        ->mapWithKeys(fn ($tagId) => [
                            $tagId => [
                                'created_by' => Auth::id(),
                            ],
                        ])
                        ->all();

                    // Synchronisation de la relation many-to-many entre le service et ses tags.
                    $service->tags()->sync($pivotData);
                }

                // Retour du service créé depuis la transaction.
                return $service;
            });
        } catch (\Throwable $e) {
            // En cas d’erreur après upload, suppression de l’image nouvellement envoyée.
            if ($uploadedImage) {
                $this->deleteImage($uploadedImage, 'services');
            }

            // Relance de l’exception pour conserver le comportement global de gestion d’erreur.
            throw $e;
        }

        // Journalisation de la création avec les données du service et les tags associés.
        logActivity("Création d'un service", $data + ['tag_ids' => $tagIds], $service);

        // Retour du service créé avec ses tags et ses relations d’audit.
        return $this->successResponse(
            $service->load('tags', 'createdBy', 'updatedBy'),
            'Service créé avec succès.'
        );
    }

    /**
     * Affiche les informations d’un service spécifique.
     *
     * Le service est recherché par son identifiant et retourné avec ses tags
     * ainsi que ses relations d’audit.
     *
     * Si aucun service correspondant n’est trouvé, une réponse d’erreur est retournée.
     *
     * @param string $id Identifiant du service à récupérer.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        // Recherche du service avec ses tags et ses relations d’audit.
        $service = Service::with('tags', 'createdBy', 'updatedBy')->find($id);

        // Gestion du cas où le service demandé n’existe pas.
        if (! $service) {
            return $this->errorResponse('Service introuvable.');
        }

        // Retour du service trouvé dans une réponse JSON standardisée.
        return $this->successResponse($service, 'Service chargé avec succès.');
    }

    /**
     * Met à jour les informations d’un service existant.
     *
     * Cette méthode gère plusieurs aspects :
     * - modification des champs simples du service ;
     * - remplacement éventuel de l’image ;
     * - synchronisation éventuelle des tags ;
     * - journalisation des anciennes et nouvelles valeurs.
     *
     * La synchronisation des tags est conditionnelle :
     * - si `tag_ids` est absent, les tags existants ne sont pas modifiés ;
     * - si `tag_ids` est présent, même vide, la relation est synchronisée.
     *
     * En cas d’erreur après l’upload d’une nouvelle image, cette image est supprimée
     * afin d’éviter les fichiers orphelins.
     *
     * @param ServiceRequest $request Requêteants ne sont pas modifiés ;
     * - si `tag_ids` est présent, même vide, la relation est contenant les données validées.
     * @param string $id Identifiant du service à modifier.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function update(ServiceRequest $request, string $id)
    {
        // Recherche du service à modifier.
        $service = Service::find($id);

        // Gestion du cas où le service demandé n’existe pas.
        if (! $service) {
            return $this->errorResponse('Service introuvable.');
        }

        // Récupération des données validées par la FormRequest.
        $data = $request->validated();

        /*
         * Si tag_ids est absent de la requête, on ne touche pas aux tags.
         * Si tag_ids est présent, même vide, on synchronise la relation.
         */
        $shouldSyncTags = array_key_exists('tag_ids', $data);

        // Normalisation du tableau des tags : valeurs uniques et réindexation du tableau.
        $tagIds = array_values(array_unique($data['tag_ids'] ?? []));

        // Sauvegarde de l’ancienne image afin de pouvoir la supprimer après remplacement réussi.
        $oldImage = $service->image_path;

        // Variable utilisée pour suivre la nouvelle image uploadée en cas d’erreur.
        $newImage = null;

        // Capture de l’état initial du service avec ses tags pour la journalisation.
        $oldValues = $service->load('tags')->toArray();

        // Suppression de `tag_ids` des données principales, car les tags sont gérés séparément via la relation.
        unset($data['tag_ids']);

        try {
            // Vérifie si une nouvelle image a été envoyée dans la requête.
            if ($request->hasFile('image')) {
                // Upload de la nouvelle image dans le dossier logique des services.
                $newImage = $this->uploadImage($request->file('image'), 'services');

                // Enregistrement du chemin de la nouvelle image dans les données à mettre à jour.
                $data['image_path'] = $newImage;
            }

            // Suppression de la clé `image`, car le modèle stocke uniquement `image_path`.
            unset($data['image']);

            // Association de la modification à l’utilisateur authentifié.
            $data['updated_by'] = Auth::id();

            // Transaction garantissant la cohérence entre la mise à jour du service et la synchronisation des tags.
            DB::transaction(function () use ($service, $data, $shouldSyncTags, $tagIds) {
                // Mise à jour des champs principaux du service.
                $service->update($data);

                // Synchronisation des tags uniquement si le champ `tag_ids` a été transmis.
                if ($shouldSyncTags) {
                    // Récupération des tags actuellement associés au service.
                    $existingTagIds = $service->tags()
                        ->pluck('tags.id')
                        ->all();

                    // Préparation des données pivot selon que l’association existe déjà ou non.
                    $pivotData = collect($tagIds)
                        ->mapWithKeys(fn ($tagId) => [
                            $tagId => in_array($tagId, $existingTagIds, true)
                                ? ['updated_by' => Auth::id()]
                                : ['created_by' => Auth::id()],
                        ])
                        ->all();

                    // Synchronisation de la relation many-to-many service/tag.
                    $service->tags()->sync($pivotData);
                }
            });
        } catch (\Throwable $e) {
            // En cas d’échec après upload, suppression de la nouvelle image non utilisée.
            if ($newImage) {
                $this->deleteImage($newImage, 'services');
            }

            // Relance de l’exception pour conserver la gestion globale des erreurs.
            throw $e;
        }

        // Suppression de l’ancienne image uniquement après réussite de la mise à jour avec une nouvelle image.
        if ($newImage && $oldImage) {
            $this->deleteImage($oldImage, 'services');

            // Journalisation spécifique du remplacement d’image.
            logActivity("Remplacement de l'image d'un service", [
                'old_image' => $oldImage,
                'new_image' => $newImage,
            ], $service);
        }

        // Préparation des données de journalisation avec l’état avant/après.
        $logData = [
            'old_value' => $oldValues,
            'new_value' => $data,
        ];

        // Ajout des tags dans la journalisation uniquement lorsqu’une synchronisation a été demandée.
        if ($shouldSyncTags) {
            $logData['new_value']['tag_ids'] = $tagIds;
        }

        // Journalisation globale de la modification du service.
        logActivity("Modification d'un service", $logData, $service);

        // Retour du service actualisé avec ses tags et ses relations d’audit.
        return $this->successResponse(
            $service->fresh()->load('tags', 'createdBy', 'updatedBy'),
            'Service modifié avec succès.'
        );
    }

    /**
     * Supprime un service existant.
     *
     * Le service est recherché par son identifiant.
     * Avant suppression, ses données et ses tags sont chargés puis journalisés
     * afin de conserver une trace complète de l’élément supprimé.
     *
     * Si une image est associée au service, elle est supprimée du stockage externe
     * après la suppression de l’enregistrement en base de données.
     *
     * @param string $id Identifiant du service à supprimer.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        // Recherche du service à supprimer.
        $service = Service::find($id);

        // Gestion du cas où le service demandé n’existe pas.
        if (! $service) {
            return $this->errorResponse('Service introuvable.');
        }

        // Conservation du chemin de l’image avant suppression du modèle.
        $image = $service->image_path;

        // Journalisation avant suppression avec les tags associés.
        logActivity("Suppression d'un service", $service->load('tags')->toArray(), $service);

        // Suppression du service en base de données.
        $service->delete();

        // Suppression de l’image associée si elle existe.
        if ($image) {
            $this->deleteImage($image, 'services');
        }

        // Retour d’une réponse de succès sans contenu métier.
        return $this->noContentSuccessResponse('Service supprimé avec succès.');
    }
}