<?php

namespace App\Modules\Sondage\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable('reference', 'votant_id', 'init_sondage_id', 'scenario', 'is_winner')]
class Vote extends Model
{
    #[Override]
    protected function casts()
    {
        return [
            'scenario'  => 'array',
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
