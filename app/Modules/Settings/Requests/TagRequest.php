<?php

namespace App\Modules\Settings\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'libelle' => ['required', 'string', 'min:2', 'max:160', Rule::unique('tags', 'libelle')->ignore($this->route()->parameter('tag'))],
            'description' => ['nullable', 'string', 'min:2']
        ];
    }
}
