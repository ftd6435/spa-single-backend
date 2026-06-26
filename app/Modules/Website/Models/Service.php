<?php

namespace App\Modules\Website\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Tag;
use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * Modèle Eloquent représentant un service du module Website.
 *
 * Ce modèle correspond à la table `services`.
 * Il permet de gérer les services proposés par l’entreprise et affichés
 * sur le site vitrine.
 *
 * Un service peut contenir :
 * - une icône ;
 * - une image associée ;
 * - un titre ;
 * - une courte description ;
 * - une description détaillée ;
 * - une liste d’avantages stockée sous forme de tableau.
 *
 * Le modèle est également lié à plusieurs entités métier :
 * - des projets associés au service ;
 * - des tags via une table pivot `service_tag` ;
 * - des utilisateurs d’audit pour la création et la dernière modification.
 *
 * La gestion de l’URL publique de l’image est assurée par le trait CloudflareUpload,
 * qui permet de reconstruire une URL exploitable à partir du chemin stocké en base.
 */
#[Fillable([
    'icon',
    'image_path',
    'title',
    'short_description',
    'description',
    'benefits',
    'created_by',
    'updated_by',
])]
class Service extends Model
{
    use CloudflareUpload;

    /**
     * Attributs calculés ajoutés automatiquement à la représentation du modèle.
     *
     * Ici, `image_url` est ajouté aux réponses JSON afin de fournir directement
     * une URL publique exploitable par le frontend, sans obliger celui-ci
     * à reconstruire l’URL à partir du champ interne `image_path`.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'image_url',
    ];

    /**
     * Définit les conversions automatiques de types pour certains attributs.
     *
     * Le champ `benefits` est converti automatiquement en tableau PHP.
     * Cela permet de manipuler les avantages du service comme une liste côté Laravel,
     * même si la donnée est stockée sous un format sérialisé ou JSON en base.
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
            'benefits' => 'array',
        ];
    }

    /**
     * Retourne l’URL publique de l’image du service.
     *
     * Cet accessor Eloquent permet d’exposer un attribut calculé `image_url`.
     * Il transforme le chemin interne `image_path` en URL complète exploitable
     * par le frontend.
     *
     * Si aucune image n’est associée au service, la méthode retourne `null`.
     *
     * @return string|null URL publique de l’image ou null si aucune image n’existe.
     */
    public function getImageUrlAttribute(): ?string
    {
        // Aucun chemin d’image n’est associé au service.
        if (! $this->image_path) {
            return null;
        }

        // Génération de l’URL publique de l’image depuis le stockage Cloudflare.
        return $this->getImageUrl($this->image_path, 'services');
    }

    /**
     * Récupère les projets associés au service.
     *
     * Relation de type one-to-many :
     * - un service peut être associé à plusieurs projets ;
     * - chaque projet appartient à un seul service.
     *
     * Cette relation permet d’accéder aux réalisations ou cas d’usage
     * rattachés à un service donné.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projects()
    {
        // Un service peut être lié à plusieurs projets.
        return $this->hasMany(Project::class);
    }

    /**
     * Récupère les tags associés au service.
     *
     * Relation de type many-to-many :
     * - un service peut avoir plusieurs tags ;
     * - un tag peut être associé à plusieurs services.
     *
     * La relation utilise la table pivot `service_tag`.
     * Cette table pivot contient également les champs d’audit :
     * - `created_by` ;
     * - `updated_by`.
     *
     * Les timestamps de la table pivot sont activés avec `withTimestamps()`,
     * ce qui permet de suivre la date de création et de mise à jour
     * de chaque association service/tag.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        // Relation many-to-many entre les services et les tags via la table pivot `service_tag`.
        return $this->belongsToMany(Tag::class, 'service_tag')
            ->withPivot(['created_by', 'updated_by'])
            ->withTimestamps();
    }

    /**
     * Récupère l’utilisateur ayant créé le service.
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
        // Relation vers l’utilisateur créateur du service.
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Récupère l’utilisateur ayant effectué la dernière modification du service.
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
        // Relation vers l’utilisateur ayant modifié le service en dernier.
        return $this->belongsTo(User::class, 'updated_by');
    }
}