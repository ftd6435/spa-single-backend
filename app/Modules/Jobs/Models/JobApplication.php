<?php

namespace App\Modules\Jobs\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'job_opening_id',
        'last_name',
        'first_name',
        'email',
        'phone',
        'cv_file',
        'drive_link',
        'status',
    ];

    public function jobOpening()
    {
        return $this->belongsTo(JobOpening::class);
    }

    public function processes()
    {
        return $this->hasMany(JobApplicationProcess::class);
    }

    public function getIsAcceptedAttribute(): bool
    {
        return $this->status === 'accepted';
    }
}