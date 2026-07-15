<?php

namespace App\Modules\Sondage\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable([
    'libelle',
    'description',
    'saison',
    'is_active',
])]
class Competition extends Model
{
    #[Override]
    protected function casts()
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function equipes()
    {
        return $this->belongsToMany(Equipes::class, 'competition_equipes', 'competition_id', 'equipe_id')
            ->withTimestamps();
    }

    public function initSondages()
    {
        return $this->hasMany(InitSondage::class);
    }
}
