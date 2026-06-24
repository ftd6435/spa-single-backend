<?php

namespace App\Modules\Jobs\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class JobApplicationProcess extends Model
{
    protected $fillable = [
        'job_application_id',
        'title',
        'description',
        'status',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function jobApplication()
    {
        return $this->belongsTo(JobApplication::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }
}
