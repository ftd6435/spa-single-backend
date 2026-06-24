<?php

namespace App\Modules\Website\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Category;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'category_id',
    'service_id',
    'title',
    'short_description',
    'description',
    'demo_link',
    'created_by',
    'updated_by',
])]
class Project extends Model
{
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

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