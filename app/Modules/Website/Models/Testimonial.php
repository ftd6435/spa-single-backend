<?php

namespace App\Modules\Website\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Eloquent représentant un témoignage du module Website.
 *
 * Ce modèle correspond à la table `testimonials`.
 * Il permet de gérer les témoignages clients affichés ou administrés
 * dans le cadre du site vitrine.
 *
 * Un témoignage est rattaché à deux entités métier principales :
 * - un projet, qui représente la réalisation concernée par le témoignage ;
 * - un client, qui représente l’auteur ou la personne associée au témoignage.
 *
 * Les champs d’audit `created_by` et `updated_by` permettent d’identifier
 * les utilisateurs responsables de la création et de la dernière modification.
 *
 * Les champs autorisés à l’insertion et à la mise à jour de masse sont définis
 * via l’attribut PHP `Fillable`.
 */
#[Fillable([
    'project_id',
    'client_id',
    'content',
    'created_by',
    'updated_by',
])]
class Testimonial extends Model
{
    /**
     * Récupère le projet associé au témoignage.
     *
     * Relation de type many-to-one :
     * - un témoignage appartient à un seul projet ;
     * - un projet peut recevoir plusieurs témoignages.
     *
     * Cette relation permet de rattacher un retour client à une réalisation précise.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        // Relation vers le projet concerné par le témoignage.
        return $this->belongsTo(Project::class);
    }

    /**
     * Récupère le client associé au témoignage.
     *
     * Relation de type many-to-one :
     * - un témoignage appartient à un seul client ;
     * - un client peut être associé à plusieurs témoignages.
     *
     * Cette relation permet d’identifier l’auteur ou la personne liée au témoignage.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        // Relation vers le client associé au témoignage.
        return $this->belongsTo(Client::class);
    }

    /**
     * Récupère l’utilisateur ayant créé le témoignage.
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
        // Relation vers l’utilisateur créateur du témoignage.
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Récupère l’utilisateur ayant effectué la dernière modification du témoignage.
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
        // Relation vers l’utilisateur ayant modifié le témoignage en dernier.
        return $this->belongsTo(User::class, 'updated_by');
    }
}