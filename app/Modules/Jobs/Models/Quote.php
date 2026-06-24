<?php

namespace App\Modules\Jobs\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = [
        'project_name',
        'description',
        'estimated_budget',
        'expected_deadline',
        'full_name',
        'email',
        'phone',
        'company',
        'status',
    ];

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }
}