<?php

namespace App\Modules\Sondage\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompetitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle'     => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'saison'      => ['required', 'string', 'max:255'],
            'is_active'   => ['boolean'],
        ];
    }
}
