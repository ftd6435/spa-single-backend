<?php

namespace App\Modules\Formation\Models;

use App\Modules\Formation\Enums\ParticipationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'formation_id',
    'participant_id',
    'frais_inscription_requis',
    'frais_inscription_paye',
    'status',
])]
class Participation extends Model
{
    use SoftDeletes;

    protected $attributes = [
        'frais_inscription_paye' => 0,
        'status' => ParticipationStatus::EnAttente->value,
    ];

    protected function casts(): array
    {
        return [
            'frais_inscription_requis' => 'decimal:2',
            'frais_inscription_paye' => 'decimal:2',
            'status' => ParticipationStatus::class,
        ];
    }

    public function formation()
    {
        return $this->belongsTo(Formation::class)->withTrashed();
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class)->withTrashed();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
