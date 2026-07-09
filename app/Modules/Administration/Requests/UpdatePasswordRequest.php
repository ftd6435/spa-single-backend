<?php

namespace App\Modules\Administration\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed', 'different:current_password'],
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'current_password.required' => "Le mot de passe actuel est requis.",

            'password.required'  => "Le nouveau mot de passe est requis.",
            'password.min'       => "Le nouveau mot de passe doit contenir au moins :min caractères.",
            'password.confirmed' => "La confirmation du mot de passe ne correspond pas.",
            'password.different' => "Le nouveau mot de passe doit être différent de l'ancien.",
        ];
    }
}
