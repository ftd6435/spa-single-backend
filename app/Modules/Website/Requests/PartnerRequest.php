<?php

namespace App\Modules\Website\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest dédiée à la validation des données d’un partenaire.
 *
 * Cette classe centralise les règles de validation utilisées lors :
 * - de la création d’un partenaire ;
 * - de la modification d’un partenaire existant.
 *
 * Elle permet de garder le contrôleur plus lisible en déléguant la validation
 * des champs à une classe spécialisée.
 *
 * La validation tient compte du contexte de la requête :
 * - en création, le nom du partenaire est obligatoire ;
 * - en modification, le nom devient optionnel afin d’autoriser les mises à jour partielles.
 *
 * Cette FormRequest valide également le logo éventuel du partenaire, notamment :
 * - le type de fichier attendu ;
 * - les formats acceptés ;
 * - la taille maximale autorisée.
 */
class PartnerRequest extends FormRequest
{
    /**
     * Détermine si l’utilisateur est autorisé à effectuer cette requête.
     *
     * La méthode retourne `true`, ce qui signifie que la requête est autorisée
     * au niveau de cette FormRequest.
     *
     * Les contrôles d’accès plus précis peuvent être gérés ailleurs dans l’application,
     * par exemple via les middlewares, les policies ou la protection des routes.
     *
     * @return bool
     */
    public function authorize()
    {
        // Autorise l’exécution de cette requête de validation.
        return true;
    }

    /**
     * Définit les règles de validation applicables aux données d’un partenaire.
     *
     * La variable `$nameRule` permet d’adapter la règle du champ `name`
     * selon le type de requête :
     * - `required` lorsque la requête est une création en POST ;
     * - `sometimes` lorsque la requête concerne une mise à jour.
     *
     * Cette approche permet d’utiliser la même FormRequest pour la création
     * et la modification, tout en permettant les mises à jour partielles.
     *
     * Règles principales :
     * - `name` : nom obligatoire en création, chaîne de 2 à 160 caractères ;
     * - `acronym` : sigle optionnel, chaîne limitée à 50 caractères ;
     * - `domain` : domaine optionnel, chaîne limitée à 160 caractères ;
     * - `description` : description optionnelle ;
     * - `logo` : image optionnelle, au format png, jpg ou jpeg, limitée à 2048 Ko.
     *
     * @return array<string, array<int, string>>
     */
    public function rules()
    {
        // Détermine si le nom du partenaire est obligatoire ou seulement validé lorsqu’il est fourni.
        $nameRule = $this->isMethod('post') ? 'required' : 'sometimes';

        // Retourne les règles de validation appliquées aux données entrantes.
        return [
            'name' => [$nameRule, 'string', 'min:2', 'max:160'],
            'acronym' => ['nullable', 'string', 'max:50'],
            'domain' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ];
    }
}