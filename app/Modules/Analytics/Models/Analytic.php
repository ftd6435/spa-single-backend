<?php

namespace App\Modules\Analytics\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['visitor_id', 'path', 'referrer', 'device', 'browser', 'os', 'country', 'ip_hash', 'created_at', 'updated_at'])]
class Analytic extends Model
{
    protected $table = 'analytics';
}
