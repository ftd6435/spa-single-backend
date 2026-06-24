<?php

namespace App\Modules\Jobs\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'label',
        'link',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function heroes()
    {
        return $this->hasMany(Hero::class);
    }

}
