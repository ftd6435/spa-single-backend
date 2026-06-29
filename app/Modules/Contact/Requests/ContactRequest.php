<?php

namespace App\Modules\Contact\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validation des données du formulaire de contact (route publique)
class ContactRequest extends FormRequest
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
            // phone et company sont optionnels — tous les visiteurs n'ont pas une entreprise
            'phone'   => ['nullable', 'string', 'max:20'],
            'company' => ['nullable', 'string', 'max:160'],
            'subject' => ['required', 'string', 'min:2', 'max:200'],
            'message' => ['required', 'string', 'min:2'],
        ];
    }
}
