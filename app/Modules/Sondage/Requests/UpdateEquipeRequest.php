<?php

namespace App\Modules\Sondage\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle'     => ['sometimes', 'string', 'max:255', Rule::unique('equipes', 'libelle')->ignore($this->route('id'))],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
