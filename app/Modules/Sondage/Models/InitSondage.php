<?php

namespace App\Modules\Sondage\Models;

use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable(
    'competition_id',
    'libelle',
    'description',
    'avantage',
    'heure_debut',
    'heure_fin',
    'niveau_vote', // array of ex: step_one => "quarter final", step_two => "semi_final", etc.
    'cadeaux', // array of ex: premier: {"250 000 GNF", "2 T-Shirts de SPA Technology", etc.}, deuxieme: {}, etc.
    'image',
    'is_active',
)]
class InitSondage extends Model
{
    use CloudflareUpload;

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return $this->getImageUrl($this->image, 'sondages');
        }

        return null;
    }

    #[Override]
    protected function casts()
    {
        return [
            'avantage'    => 'array',
            'niveau_vote' => 'array',
            'cadeaux'     => 'array',
            'is_active'   => 'boolean',
        ];
    }

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function rencontres()
    {
        return $this->hasMany(Rencontre::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
