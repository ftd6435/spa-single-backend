<?php

namespace App\Modules\Sondage\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable('reference', 'votant_id', 'init_sondage_id', 'scenario', 'is_winner', 'ip_address')]
class Vote extends Model
{
    #[Override]
    protected function casts()
    {
        return [
            'scenario'  => 'array', // From the the init_sondage.niveau_vote, votant will determine the scenario of team winners, next matches, guess score, until the last niveau_vote of init_sondage
            'is_winner' => 'boolean',
        ];
    }

    public function votant()
    {
        return $this->belongsTo(Votant::class);
    }

    public function initSondage()
    {
        return $this->belongsTo(InitSondage::class);
    }
}
