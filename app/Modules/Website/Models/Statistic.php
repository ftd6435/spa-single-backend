<?php

namespace App\Modules\Website\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable([
    'label',
    'value',
    'unit',
    'created_by',
    'updated_by',
])]
class Statistic extends Model
{
    #[Override]
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
        ];
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
