<?php

namespace App\Modules\Sondage\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('name', 'telephone')]
class Votant extends Model
{
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
