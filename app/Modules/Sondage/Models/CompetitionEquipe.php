<?php

namespace App\Modules\Sondage\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('equipe_id', 'competition_id')]
class CompetitionEquipe extends Model
{
    public function equipe()
    {
        return $this->belongsTo(Equipes::class, 'equipe_id');
    }

    public function competition()
    {
        return $this->belongsTo(Competition::class, 'competition_id');
    }
}
