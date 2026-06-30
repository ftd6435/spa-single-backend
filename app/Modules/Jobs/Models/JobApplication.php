<?php

namespace App\Modules\Jobs\Models;

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
    public function jobOpening()
    {
        return $this->belongsTo(JobOpening::class);
    }

    public function processes()
    {
        return $this->hasMany(JobApplicationProcess::class);
    }
}
