<?php

namespace App\Modules\Website\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'first_name',
    'last_name',
    'job_title',
    'created_by',
    'updated_by',
])]
class Client extends Model
{
    public function testimonials()
    {
        return $this->hasMany(Testimonial::class);
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
