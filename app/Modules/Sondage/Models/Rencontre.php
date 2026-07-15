<?php

namespace App\Modules\Sondage\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable(
    'home_team_id',
    'away_team_id',
    'init_sondage_id',
    'phase',
    'date_rencontre',
    'heure_rencontre',
    'team_winner_id',
    'final_score',
    'is_active',
)]
class Rencontre extends Model
{
    #[Override]
    protected function casts()
    {
        return [
            'date_rencontre' => 'date',
            'is_active'      => 'boolean',
        ];
    }

    public function homeTeam()
    {
        return $this->belongsTo(Equipes::class, 'home_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo(Equipes::class, 'away_team_id');
    }

    public function winner()
    {
        return $this->belongsTo(Equipes::class, 'team_winner_id');
    }

    public function initSondage()
    {
        return $this->belongsTo(InitSondage::class);
    }
}
