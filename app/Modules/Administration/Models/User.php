<?php

namespace App\Modules\Administration\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\CloudflareUpload;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'telephone', 'status', 'avatar', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, CloudflareUpload;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean'
        ];
    }

    protected $appends = [
        'avatar_url',
    ];

    /**
     * Get the profile photo URL attribute.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar) {
            return $this->getImageUrl($this->avatar, 'avatars');
        }
        // Return default avatar
        return $this->defaultProfilePhotoUrl();
    }

    /**
     * Get the default profile photo URL.
     */
    protected function defaultProfilePhotoUrl(): string
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=7F9CF5&background=EBF4FF';
    }
}
