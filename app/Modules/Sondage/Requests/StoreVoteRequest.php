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
            'name'                     => ['required', 'string', 'max:255'],
            'telephone'                => ['required', 'string', 'max:30'],
            'init_sondage_id'          => ['required', 'integer', 'exists:init_sondages,id'],
            'scenario'                 => ['required', 'array', 'min:1'],
            'scenario.*.niveau'        => ['required', 'string'],
            'scenario.*.home_team_name' => ['required', 'string'],
            'scenario.*.away_team_name' => ['nullable', 'string'],
            'scenario.*.score'         => ['nullable', 'string'],
        ];
    }
}
