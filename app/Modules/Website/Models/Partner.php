<?php

namespace App\Modules\Website\Models;

use App\Modules\Administration\Models\User;
use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Eloquent représentant un partenaire du module Website.
 *
 * Ce modèle correspond à la table `partners`.
 * Il permet de gérer les informations des partenaires affichés ou administrés
 * dans le cadre du site vitrine.
 *
 * Un partenaire peut contenir :
 * - un nom ;
 * - un sigle ou acronyme ;
 * - un domaine d’activité ;
 * - une description ;
 * - un chemin de logo stocké via `logo_path`.
 *
 * La gestion de l’URL publique du logo est assurée par le trait CloudflareUpload,
 * qui permet de reconstruire l’URL complète à partir du chemin stocké en base.
 *
 * Les champs d’audit `created_by` et `updated_by` permettent d’identifier
 * les utilisateurs responsables de la création et de la dernière modification.
 */
#[Fillable([
    'name',
    'acronym',
    'domain',
    'description',
    'logo_path',
    'created_by',
    'updated_by',
])]
class Partner extends Model
{
    use CloudflareUpload;

    /**
     * Attributs calculés ajoutés automatiquement à la représentation du modèle.
     *
     * Ici, `logo_url` est ajouté aux réponses JSON afin de fournir directement
     * une URL exploitable côté client, sans exposer uniquement le chemin brut
     * stocké dans `logo_path`.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'logo_url',
    ];

    /**
     * Retourne l’URL publique du logo du partenaire.
     *
     * Cet accessor Eloquent permet d’exposer un attribut calculé `logo_url`.
     * Il transforme le chemin interne `logo_path` en URL complète exploitable
     * par le frontend.
     *
     * Si aucun logo n’est associé au partenaire, la méthode retourne `null`.
     *
     * @return string|null URL publique du logo ou null si aucun logo n’existe.
     */
    public function getLogoUrlAttribute(): ?string
    {
        // Aucun logo n’est associé au partenaire.
        if (! $this->logo_path) {
            return null;
        }

        // Génération de l’URL publique du logo depuis le stockage Cloudflare.
        return $this->getImageUrl($this->logo_path, 'partners');
    }

    /**
     * Récupère l’utilisateur ayant créé le partenaire.
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
        // Relation vers l’utilisateur créateur du partenaire.
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Récupère l’utilisateur ayant effectué la dernière modification du partenaire.
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
        // Relation vers l’utilisateur ayant modifié le partenaire en dernier.
        return $this->belongsTo(User::class, 'updated_by');
    }
}