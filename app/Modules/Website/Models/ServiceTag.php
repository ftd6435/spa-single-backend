<?php

namespace App\Modules\Website\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Tag;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'service_id',
    'tag_id',
    'created_by',
    'updated_by',
])]
class ServiceTag extends Model
{
    protected $table = 'service_tag';

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
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