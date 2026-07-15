<?php

namespace App\Modules\Sondage\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRencontreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'home_team_id'    => ['required', 'integer', 'exists:equipes,id', 'different:away_team_id'],
            'away_team_id'    => ['required', 'integer', 'exists:equipes,id'],
            'init_sondage_id' => ['required', 'integer', 'exists:init_sondages,id'],
            'phase'           => ['required', 'string', 'max:30'],
            'date_rencontre'  => ['nullable', 'date'],
            'heure_rencontre' => ['nullable', 'date_format:H:i'],
            'team_winner_id'  => ['nullable', 'integer', 'exists:equipes,id'],
            'final_score'     => ['nullable', 'string', 'max:20'],
            'is_active'       => ['boolean'],
        ];
    }
}
