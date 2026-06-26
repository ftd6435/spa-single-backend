<?php

namespace App\Modules\Website\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest dédiée à la validation des données d’un client.
 *
 * Cette classe centralise les règles de validation utilisées lors :
 * - de la création d’un client ;
 * - de la modification d’un client existant.
 *
 * Elle permet de séparer la logique de validation du contrôleur, ce qui rend
 * le code plus lisible, plus maintenable et plus conforme aux bonnes pratiques Laravel.
 *
 * La validation est adaptée selon le type de requête HTTP :
 * - en création, certains champs sont obligatoires ;
 * - en modification, ces mêmes champs deviennent optionnels afin de permettre
 *   des mises à jour partielles.
 */
class ClientRequest extends FormRequest
{
    /**
     * Détermine si l’utilisateur est autorisé à effectuer cette requête.
     *
     * La méthode retourne `true`, ce qui signifie que la requête est autorisée
     * au niveau de cette FormRequest.
     *
     * La gestion fine des permissions peut être assurée ailleurs, par exemple
     * via les middlewares, les policies ou les règles d’accès des routes.
     *
     * @return bool
     */
    public function authorize()
    {
        // Autorise l’exécution de cette requête de validation.
        return true;
    }

    /**
     * Définit les règles de validation applicables aux données du client.
     *
     * La variable `$requiredOnCreate` permet d’adapter les règles selon le contexte :
     * - `required` lorsque la requête est une création en POST ;
     * - `sometimes` lorsque la requête concerne une mise à jour.
     *
     * Cette approche permet d’utiliser la même FormRequest pour la création
     * et la modification, tout en autorisant les mises à jour partielles.
     *
     * Règles principales :
     * - `first_name` : prénom obligatoire en création, chaîne de 2 à 160 caractères ;
     * - `last_name` : nom obligatoire en création, chaîne de 2 à 160 caractères ;
     * - `job_title` : poste optionnel, chaîne limitée à 160 caractères.
     *
     * @return array<string, array<int, string>>
     */
    public function rules()
    {
        // Détermine si les champs principaux doivent être obligatoires ou seulement présents lorsqu’ils sont fournis.
        $requiredOnCreate = $this->isMethod('post') ? 'required' : 'sometimes';

        // Retourne les règles de validation appliquées aux données entrantes.
        return [
            'first_name' => [$requiredOnCreate, 'string', 'min:2', 'max:160'],
            'last_name' => [$requiredOnCreate, 'string', 'min:2', 'max:160'],
            'job_title' => ['nullable', 'string', 'max:160'],
        ];
    }
}