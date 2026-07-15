<?php

namespace App\Modules\Sondage\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRencontreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'home_team_id'    => ['sometimes', 'integer', 'exists:equipes,id', 'different:away_team_id'],
            'away_team_id'    => ['sometimes', 'integer', 'exists:equipes,id'],
            'init_sondage_id' => ['sometimes', 'integer', 'exists:init_sondages,id'],
            'phase'           => ['sometimes', 'string', Rule::in([
                'groupe_stage', 'round_of_32', 'round_of_16', 'quarter_final', 'semi_final', 'final',
            ])],
            'date_rencontre'  => ['sometimes', 'nullable', 'date'],
            'heure_rencontre' => ['sometimes', 'nullable', 'date_format:H:i'],
            'team_winner_id'  => ['sometimes', 'nullable', 'integer', 'exists:equipes,id'],
            'final_score'     => ['sometimes', 'nullable', 'string', 'max:20'],
            'is_active'       => ['sometimes', 'boolean'],
        ];
    }
}
