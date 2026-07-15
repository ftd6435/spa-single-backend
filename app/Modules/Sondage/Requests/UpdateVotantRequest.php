<?php

namespace App\Modules\Sondage\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVotantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['sometimes', 'string', 'max:255'],
            'telephone' => ['sometimes', 'string', 'max:30', Rule::unique('votants', 'telephone')->ignore($this->route('id'))],
        ];
    }
}
