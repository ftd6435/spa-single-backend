<?php

namespace App\Modules\Offer\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable(['name', 'description', 'status', 'created_by', 'updated_by'])]
class OfferType extends Model
{
    protected $attributes = ['status' => true];

    #[Override]
    protected function casts()
    {
        return [
            'status' => 'boolean',
        ];
    }

    // Utilisateur qui a créé ce type d'offre
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