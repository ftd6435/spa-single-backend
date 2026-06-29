<?php

namespace App\Modules\Settings\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Website\Models\Project;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable('libelle', 'description', 'status', 'created_by', 'updated_by')]
class Category extends Model
{

    #[Override]
    protected function casts()
    {
        return [
            'status' => 'boolean' // 1 = true ou 0 = false
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

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}