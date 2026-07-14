<?php

namespace App\Modules\Formation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFormationCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle' => [
                'sometimes',
                'string',
                'min:2',
                'max:160',
                Rule::unique('formation_categories', 'libelle')->ignore($this->route('formationCategory')),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
