<?php

namespace App\Modules\Sondage\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompetitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle'     => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'saison'      => ['sometimes', 'string', 'max:255'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }
}
