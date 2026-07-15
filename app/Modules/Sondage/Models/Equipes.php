<?php

namespace App\Modules\Sondage\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'libelle',
    'description',
])]
class Equipes extends Model
{
    public function competitions()
    {
        return $this->belongsToMany(Competition::class, 'competition_equipes', 'equipe_id', 'competition_id')
            ->withTimestamps();
    }
}
