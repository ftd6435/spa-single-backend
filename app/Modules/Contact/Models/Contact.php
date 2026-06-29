<?php

namespace App\Modules\Contact\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

// Message envoyé via le formulaire de contact du site
#[Fillable('name', 'email', 'phone', 'company', 'subject', 'message', 'created_by', 'updated_by')]
class Contact extends Model
{
    // created_by est null si le message vient d'un visiteur anonyme (route publique)
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
