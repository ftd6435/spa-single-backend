<?php

namespace App\Modules\Website\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest dédiée à la validation des données d’un service.
 *
 * Cette classe centralise les règles de validation utilisées lors :
 * - de la création d’un service ;
 * - de la modification d’un service existant.
 *
 * Elle permet de garder la logique de validation en dehors du contrôleur,
 * ce qui améliore la lisibilité, la maintenabilité et la cohérence du code.
 *
 * La validation prend en charge :
 * - les champs textuels du service ;
 * - l’image associée au service ;
 * - la liste des avantages ;
 * - les tags associés au service via un tableau d’identifiants.
 *
 * Les tags sont volontairement validés ici à travers le champ `tag_ids`,
 * car leur gestion métier est liée directement à la création ou à la mise à jour
 * d’un service.
 */
class ServiceRequest extends FormRequest
{
    /**
     * Détermine si l’utilisateur est autorisé à effectuer cette requête.
     *
     * La méthode retourne `true`, ce qui signifie que la requête est autorisée
     * au niveau de cette FormRequest.
     *
     * Les contrôles d’accès plus précis peuvent être gérés ailleurs dans
     * l’application, par exemple via les middlewares, les policies ou les routes
     * protégées.
     *
     * @return bool
     */
    public function authorize()
    {
        // Autorise l’exécution de cette requête de validation.
        return true;
    }

    /**
     * Définit les règles de validation applicables aux données d’un service.
     *
     * La variable `$requiredOnCreate` permet d’adapter certaines règles selon
     * le contexte de la requête :
     * - `required` lorsque la requête est une création en POST ;
     * - `sometimes` lorsque la requête concerne une mise à jour.
     *
     * Cette approche permet d’utiliser la même FormRequest pour la création
     * et la modification, tout en permettant des mises à jour partielles.
     *
     * Règles principales :
     * - `icon` : icône optionnelle, stockée sous forme de chaîne ;
     * - `image` : image optionnelle, limitée aux formats png, jpg, jpeg et webp ;
     * - `title` : titre obligatoire en création, chaîne de 2 à 160 caractères ;
     * - `short_description` : courte description obligatoire en création ;
     * - `description` : description détaillée obligatoire en création ;
     * - `benefits` : tableau optionnel contenant les avantages du service ;
     * - `benefits.*` : chaque avantage doit être une chaîne d’au moins 2 caractères ;
     * - `tag_ids` : tableau optionnel d’identifiants de tags ;
     * - `tag_ids.*` : chaque tag doit être unique, entier et exister dans la table `tags`.
     *
     * @return array<string, array<int, string>>
     */
    public function rules()
    {
        // Détermine si les champs principaux doivent être obligatoires ou seulement validés lorsqu’ils sont fournis.
        $requiredOnCreate = $this->isMethod('post') ? 'required' : 'sometimes';

        // Retourne les règles de validation appliquées aux données entrantes.
        return [
            'icon' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'title' => [$requiredOnCreate, 'string', 'min:2', 'max:160'],
            'short_description' => [$requiredOnCreate, 'string', 'min:2', 'max:1000'],
            'description' => [$requiredOnCreate, 'string', 'min:2'],
            'benefits' => ['nullable', 'array'],
            'benefits.*' => ['string', 'min:2'],

            /*
             * Les tags du service sont gérés ici, pas dans un ServiceTagRequest.
             * tag_ids absent sur update => on ne modifie pas les tags.
             * tag_ids présent avec [] => on retire tous les tags.
             */
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'distinct', 'exists:tags,id'],
        ];
    }
}