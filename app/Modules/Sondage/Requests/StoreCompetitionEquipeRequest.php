<?php

namespace App\Modules\Sondage\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompetitionEquipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipe_id' => [
                'required',
                'integer',
                'exists:equipes,id',
                Rule::unique('competition_equipes', 'equipe_id')->where('competition_id', $this->route('competitionId')),
            ],
        ];
    }
}
