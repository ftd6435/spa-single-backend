<?php

namespace App\Modules\Sondage\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInitSondageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'competition_id' => ['sometimes', 'integer', 'exists:competitions,id'],
            'libelle'        => ['sometimes', 'string', 'max:255'],
            'description'    => ['sometimes', 'nullable', 'string'],
            'avantage'       => ['sometimes', 'nullable', 'array'],
            'heure_debut'    => ['sometimes', 'nullable', 'date_format:H:i'],
            'heure_fin'      => ['sometimes', 'nullable', 'date_format:H:i', 'after:heure_debut'],
            'niveau_vote'    => ['sometimes', 'nullable', 'array'],
            'cadeaux'        => ['sometimes', 'nullable', 'array'],
            'image'          => ['sometimes', 'nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active'      => ['sometimes', 'boolean'],
        ];
    }
}
