<?php

namespace App\Modules\Website\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Eloquent représentant un client du module Website.
 *
 * Ce modèle correspond à la table `clients`.
 * Il permet de gérer les informations principales d’un client pouvant être
 * associé à un ou plusieurs témoignages.
 *
 * Les champs autorisés à l’insertion et à la mise à jour de masse sont définis
 * via l’attribut PHP `Fillable`.
 *
 * Le modèle contient également des relations d’audit permettant d’identifier :
 * - l’utilisateur ayant créé le client ;
 * - l’utilisateur ayant effectué la dernière modification.
 */
#[Fillable([
    'first_name',
    'last_name',
    'job_title',
    'created_by',
    'updated_by',
])]
class Client extends Model
{
    /**
     * Récupère les témoignages associés au client.
     *
     * Relation de type one-to-many :
     * - un client peut avoir plusieurs témoignages ;
     * - chaque témoignage appartient à un seul client.
     *
     * Cette relation permet notamment d’accéder aux témoignages liés au client
     * depuis le modèle Client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function testimonials()
    {
        // Un client peut être lié à plusieurs témoignages.
        return $this->hasMany(Testimonial::class);
    }

    /**
     * Récupère l’utilisateur ayant créé le client.
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
        // Relation vers l’utilisateur créateur du client.
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Récupère l’utilisateur ayant effectué la dernière modification du client.
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
        // Relation vers l’utilisateur ayant modifié le client en dernier.
        return $this->belongsTo(User::class, 'updated_by');
    }
}