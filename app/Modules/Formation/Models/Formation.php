<?php

namespace App\Modules\Formation\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Formation\Enums\FormationStatus;
use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'formation_category_id',
    'libelle',
    'short_description',
    'description',
    'date_debut',
    'date_fin',
    'nombre_places',
    'lieu_formation',
    'date_fin_inscription',
    'frais_inscription',
    'frais_formation',
    'status',
    'thumbnail_path',
    'is_active',
    'created_by',
    'updated_by',
])]
class Formation extends Model
{
    use CloudflareUpload, SoftDeletes;

    public const THUMBNAIL_STORAGE_PATH = 'formations/thumbnails';

    protected $attributes = [
        'status' => FormationStatus::EnAttente->value,
        'is_active' => true,
    ];

    protected $appends = ['thumbnail_url'];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'date_fin_inscription' => 'date',
            'frais_inscription' => 'decimal:2',
            'frais_formation' => 'decimal:2',
            'status' => FormationStatus::class,
            'is_active' => 'boolean',
        ];
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path
            ? $this->getImageUrl($this->thumbnail_path, self::THUMBNAIL_STORAGE_PATH)
            : null;
    }

    public function category()
    {
        return $this->belongsTo(FormationCategory::class, 'formation_category_id')->withTrashed();
    }

    public function images()
    {
        return $this->hasMany(FormationImage::class);
    }

    public function participations()
    {
        return $this->hasMany(Participation::class);
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
