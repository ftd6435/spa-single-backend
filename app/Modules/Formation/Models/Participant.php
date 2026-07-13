<?php

namespace App\Modules\Formation\Models;

use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nom', 'prenom', 'telephone', 'adresse', 'avatar_path'])]
class Participant extends Model
{
    use CloudflareUpload, SoftDeletes;

    public const AVATAR_STORAGE_PATH = 'formations/participants';

    protected $appends = ['avatar_url'];

    public static function normalizeTelephone(string $telephone): string
    {
        return preg_replace('/[\s\-()]+/u', '', trim($telephone)) ?? trim($telephone);
    }

    public function setTelephoneAttribute(string $telephone): void
    {
        $this->attributes['telephone'] = self::normalizeTelephone($telephone);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path
            ? $this->getImageUrl($this->avatar_path, self::AVATAR_STORAGE_PATH)
            : null;
    }

    public function participations()
    {
        return $this->hasMany(Participation::class);
    }
}
