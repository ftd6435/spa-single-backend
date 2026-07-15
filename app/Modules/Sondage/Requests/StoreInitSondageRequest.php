<?php

namespace App\Modules\Sondage\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInitSondageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'competition_id' => ['required', 'integer', 'exists:competitions,id'],
            'libelle'        => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'avantage'       => ['nullable', 'array'],
            'heure_debut'    => ['nullable', 'date_format:H:i'],
            'heure_fin'      => ['nullable', 'date_format:H:i', 'after:heure_debut'],
            'niveau_vote'    => ['nullable', 'array'],
            'cadeaux'        => ['nullable', 'array'],
            'image'          => ['nullable', 'string'],
            'is_active'      => ['boolean'],
        ];
    }
}
