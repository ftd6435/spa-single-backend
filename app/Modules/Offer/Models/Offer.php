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
            'features' => 'array',
            'is_popular' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function offerType()
    {
        return $this->belongsTo(OfferType::class, 'offer_type_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}