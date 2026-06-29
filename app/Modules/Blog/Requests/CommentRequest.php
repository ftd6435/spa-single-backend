<?php

namespace App\Modules\Blog\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validation des données pour la création d'un commentaire (route publique)
class CommentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'    => ['required', 'string', 'min:2', 'max:160'],
            'email'   => ['required', 'email', 'max:255'],
            'content' => ['required', 'string', 'min:2'],
        ];
    }
}
