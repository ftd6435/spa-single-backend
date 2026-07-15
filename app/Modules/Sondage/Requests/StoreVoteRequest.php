<?php

namespace App\Modules\Sondage\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'votant_id'                => ['required', 'integer', 'exists:votants,id'],
            'init_sondage_id'          => ['required', 'integer', 'exists:init_sondages,id'],
            'scenario'                 => ['required', 'array', 'min:1'],
            'scenario.*.niveau'        => ['required', 'string'],
            'scenario.*.home_team_name' => ['required', 'string'],
            'scenario.*.away_team_name' => ['required', 'string'],
            'scenario.*.score'         => ['required', 'string'],
        ];
    }
}
