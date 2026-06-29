<?php

namespace App\Modules\Website\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest dédiée à la validation des données d’une statistique.
 *
 * Cette classe centralise les règles de validation utilisées lors :
 * - de la création d’une statistique ;
 * - de la modification d’une statistique existante.
 *
 * Elle permet de séparer la logique de validation du contrôleur afin de garder
 * un code plus clair, plus maintenable et conforme aux bonnes pratiques Laravel.
 *
 * Une statistique correspond généralement à un chiffre clé affiché sur le site
 * vitrine, composé d’un libellé, d’une valeur numérique et éventuellement
 * d’une unité.
 *
 * La validation est adaptée selon le type de requête HTTP :
 * - en création, les champs principaux sont obligatoires ;
 * - en modification, ces mêmes champs deviennent optionnels afin de permettre
 *   des mises à jour partielles.
 */
class StatisticRequest extends FormRequest
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
     * Définit les règles de validation applicables aux données d’une statistique.
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
     * - `label` : libellé obligatoire en création, chaîne de 2 à 160 caractères ;
     * - `value` : valeur numérique obligatoire en création, supérieure ou égale à 0 ;
     * - `unit` : unité optionnelle, chaîne limitée à 20 caractères.
     *
     * @return array<string, array<int, string>>
     */
    public function rules()
    {
        // Détermine si les champs principaux doivent être obligatoires ou seulement validés lorsqu’ils sont fournis.
        $requiredOnCreate = $this->isMethod('post') ? 'required' : 'sometimes';

        // Retourne les règles de validation appliquées aux données entrantes.
        return [
            'label' => [$requiredOnCreate, 'string', 'min:2', 'max:160'],
            'value' => [$requiredOnCreate, 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:20'],
        ];
    }
}