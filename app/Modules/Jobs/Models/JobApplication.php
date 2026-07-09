<?php

namespace App\Modules\Jobs\Models;

use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'job_opening_id',
    'last_name',
    'first_name',
    'email',
    'phone',
    'cv_file',
    'drive_link',
    'status',
])]
class JobApplication extends Model
{
    use CloudflareUpload;

    protected $appends = [
        'cv_file_url',
    ];

    /**
     * Get the profile photo URL attribute.
     */
    public function getCvFileUrlAttribute(): ?string
    {
        if ($this->cv_file) {
            return $this->getFileUrl($this->cv_file, 'candidatures');
        }
        // Return default avatar
        return null;
    }

    public function jobOpening()
    {
        return $this->belongsTo(JobOpening::class);
    }

    public function processes()
    {
        return $this->hasMany(JobApplicationProcess::class);
    }
}
