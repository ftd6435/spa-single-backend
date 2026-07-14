<?php

namespace App\Modules\Formation\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'participation_id',
    'montant',
    'methode_paiement',
    'date_paiement',
    'commentaire',
    'created_by',
    'updated_by',
])]
class Payment extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'date_paiement' => 'date',
        ];
    }

    public function participation()
    {
        return $this->belongsTo(Participation::class);
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
