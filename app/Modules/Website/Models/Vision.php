<?php

namespace App\Modules\Website\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Eloquent représentant une vision du module Website.
 *
 * Ce modèle correspond à la table `visions`.
 * Il permet de gérer les contenus de type vision, généralement utilisés
 * pour présenter une orientation, une philosophie, une conviction ou
 * un message institutionnel de l’entreprise.
 *
 * Une vision est généralement composée :
 * - d’un titre ;
 * - d’une description ;
 * - d’un auteur ou responsable associé.
 *
 * Les champs d’audit `created_by` et `updated_by` permettent d’identifier
 * les utilisateurs responsables de la création et de la dernière modification.
 *
 * Les champs autorisés à l’insertion et à la mise à jour de masse sont définis
 * via l’attribut PHP `Fillable`.
 */
#[Fillable([
    'title',
    'description',
    'author',
    'created_by',
    'updated_by',
])]
class Vision extends Model
{
    /**
     * Récupère l’utilisateur ayant créé la vision.
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
        // Relation vers l’utilisateur créateur de la vision.
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Récupère l’utilisateur ayant effectué la dernière modification de la vision.
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
        // Relation vers l’utilisateur ayant modifié la vision en dernier.
        return $this->belongsTo(User::class, 'updated_by');
    }
}