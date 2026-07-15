<?php

namespace App\Modules\Sondage\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle'     => ['required', 'string', 'max:255', 'unique:equipes,libelle'],
            'description' => ['nullable', 'string'],
        ];
    }
}
