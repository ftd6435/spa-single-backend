<?php

namespace App\Modules\Website\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest dédiée à la validation des données d’un projet.
 *
 * Cette classe centralise les règles de validation utilisées lors :
 * - de la création d’un projet ;
 * - de la modification d’un projet existant.
 *
 * Elle permet de séparer la logique de validation du contrôleur afin de garder
 * un code plus clair, plus maintenable et conforme aux bonnes pratiques Laravel.
 *
 * La validation est adaptée selon le type de requête HTTP :
 * - en création, les champs principaux du projet sont obligatoires ;
 * - en modification, ces mêmes champs deviennent optionnels afin de permettre
 *   des mises à jour partielles.
 *
 * Le projet peut être rattaché :
 * - à une catégorie obligatoire en création ;
 * - à un service optionnel ;
 * - à un lien de démonstration optionnel.
 */
class ProjectRequest extends FormRequest
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
     * Définit les règles de validation applicables aux données d’un projet.
     *
     * La variable `$requiredOnCreate` permet d’adapter certaines règles selon
     * le contexte de la requête :
     * - `required` lorsque la requête est une création en POST ;
     * - `sometimes` lorsque la requête concerne une mise à jour.
     *
     * Cette approche permet d’utiliser la même FormRequest pour la création
     * et la modification, tout en autorisant les mises à jour partielles.
     *
     * Règles principales :
     * - `category_id` : catégorie obligatoire en création, doit exister dans `categories` ;
     * - `service_id` : service optionnel, doit exister dans `services` s’il est fourni ;
     * - `title` : titre obligatoire en création, chaîne de 2 à 160 caractères ;
     * - `short_description` : courte description obligatoire en création, limitée à 1000 caractères ;
     * - `description` : description détaillée obligatoire en création ;
     * - `demo_link` : lien optionnel, valide comme URL et limité à 255 caractères.
     *
     * @return array<string, array<int, string>>
     */
    public function rules()
    {
        // Détermine si les champs principaux doivent être obligatoires ou seulement validés lorsqu’ils sont fournis.
        $requiredOnCreate = $this->isMethod('post') ? 'required' : 'sometimes';

        // Retourne les règles de validation appliquées aux données entrantes.
        return [
            'category_id' => [$requiredOnCreate, 'integer', 'exists:categories,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'title' => [$requiredOnCreate, 'string', 'min:2', 'max:160'],
            'short_description' => [$requiredOnCreate, 'string', 'min:2', 'max:1000'],
            'description' => [$requiredOnCreate, 'string', 'min:2'],
            'demo_link' => ['nullable', 'url', 'max:255'],
        ];
    }
}