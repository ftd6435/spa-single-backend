<?php

namespace App\Modules\Formation\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['formation_id', 'image_path', 'draft_token', 'uploaded_by'])]
class FormationImage extends Model
{
    public const STORAGE_PATH = 'formations/content';

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
