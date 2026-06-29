<?php

namespace App\Modules\Website\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Category;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Eloquent représentant un projet du module Website.
 *
 * Ce modèle correspond à la table `projects`.
 * Il permet de gérer les projets ou réalisations affichés sur le site vitrine
 * et administrés depuis le back-office.
 *
 * Un projet est lié à plusieurs entités métier :
 * - une catégorie, permettant de classer le projet ;
 * - un service, représentant le service associé au projet ;
 * - des témoignages, représentant les retours clients liés au projet.
 *
 * Les champs d’audit `created_by` et `updated_by` permettent d’identifier
 * les utilisateurs responsables de la création et de la dernière modification.
 *
 * Les champs autorisés à l’insertion et à la mise à jour de masse sont définis
 * via l’attribut PHP `Fillable`.
 */
#[Fillable([
    'category_id',
    'service_id',
    'title',
    'short_description',
    'description',
    'demo_link',
    'created_by',
    'updated_by',
])]
class Project extends Model
{
    /**
     * Récupère la catégorie associée au projet.
     *
     * Relation de type many-to-one :
     * - un projet appartient à une seule catégorie ;
     * - une catégorie peut regrouper plusieurs projets.
     *
     * Cette relation permet de classer les projets selon une catégorie
     * fonctionnelle ou métier.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        // Relation vers la catégorie associée au projet.
        return $this->belongsTo(Category::class);
    }

    /**
     * Récupère le service associé au projet.
     *
     * Relation de type many-to-one :
     * - un projet appartient à un seul service ;
     * - un service peut être lié à plusieurs projets.
     *
     * Cette relation permet de rattacher un projet à l’offre ou au service
     * concerné.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        // Relation vers le service associé au projet.
        return $this->belongsTo(Service::class);
    }

    /**
     * Récupère les témoignages associés au projet.
     *
     * Relation de type one-to-many :
     * - un projet peut recevoir plusieurs témoignages ;
     * - chaque témoignage appartient à un seul projet.
     *
     * Cette relation permet d’accéder aux retours clients liés au projet.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function testimonials()
    {
        // Un projet peut être lié à plusieurs témoignages.
        return $this->hasMany(Testimonial::class);
    }

    /**
     * Récupère l’utilisateur ayant créé le projet.
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
        // Relation vers l’utilisateur créateur du projet.
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Récupère l’utilisateur ayant effectué la dernière modification du projet.
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
        // Relation vers l’utilisateur ayant modifié le projet en dernier.
        return $this->belongsTo(User::class, 'updated_by');
    }
}