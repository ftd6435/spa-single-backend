<?php

namespace App\Modules\Website\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * Modèle Eloquent représentant une statistique du module Website.
 *
 * Ce modèle correspond à la table `statistics`.
 * Il permet de gérer les chiffres clés affichés sur le site vitrine,
 * comme par exemple :
 * - le nombre de projets réalisés ;
 * - le nombre de clients accompagnés ;
 * - le nombre d’années d’expérience ;
 * - tout autre indicateur chiffré valorisé côté public.
 *
 * Une statistique est généralement composée :
 * - d’un libellé ;
 * - d’une valeur numérique ;
 * - d’une unité optionnelle.
 *
 * Les champs d’audit `created_by` et `updated_by` permettent d’identifier
 * les utilisateurs responsables de la création et de la dernière modification.
 *
 * Les champs autorisés à l’insertion et à la mise à jour de masse sont définis
 * via l’attribut PHP `Fillable`.
 */
#[Fillable([
    'label',
    'value',
    'unit',
    'created_by',
    'updated_by',
])]
class Statistic extends Model
{
    /**
     * Définit les conversions automatiques de types pour certains attributs.
     *
     * Le champ `value` est converti automatiquement en nombre décimal
     * avec deux chiffres après la virgule.
     *
     * Cela garantit une représentation cohérente des valeurs statistiques,
     * notamment lorsque celles-ci sont retournées dans les réponses API
     * ou manipulées dans l’application.
     *
     * L’attribut `Override` indique explicitement que cette méthode redéfinit
     * une méthode attendue par le modèle parent Eloquent.
     *
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
        ];
    }

    /**
     * Récupère l’utilisateur ayant créé la statistique.
     *
     * Cette relation utilise la colonne `created_by` comme clé étrangère
     * vers le modèle User du module Administration.
     *
     * Elle permet d’assurer la traçabilité de la création de l’enregistrement.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        // Relation vers l’utilisateur créateur de la statistique.
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Récupère l’utilisateur ayant effectué la dernière modification de la statistique.
     *
     * Cette relation utilise la colonne `updated_by` comme clé étrangère
     * vers le modèle User du module Administration.
     *
     * Elle permet d’assurer la traçabilité des modifications apportées
     * à l’enregistrement.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy()
    {
        // Relation vers l’utilisateur ayant modifié la statistique en dernier.
        return $this->belongsTo(User::class, 'updated_by');
    }
}