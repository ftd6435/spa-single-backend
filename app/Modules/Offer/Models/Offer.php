<?php

namespace App\Modules\Offer\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable('offer_type_id', 'plan', 'price', 'features', 'is_popular', 'created_by', 'updated_by')]
class Offer extends Model
{
    #[Override]
    protected function casts()
    {
        return [
            // features est stocké en JSON en base, Laravel le convertit automatiquement en tableau PHP
            'features'   => 'array',
            'is_popular' => 'boolean',
            // decimal:2 assure que le prix est toujours retourné avec 2 décimales (ex: 29.99)
            'price'      => 'decimal:2',
        ];
    }

    // Type d'offre auquel cette offre appartient (ex: Mensuel, Annuel)
    public function offerType()
    {
        return $this->belongsTo(OfferType::class, 'offer_type_id');
    }

    // Utilisateur qui a créé cette offre
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Utilisateur qui a effectué la dernière modification
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
