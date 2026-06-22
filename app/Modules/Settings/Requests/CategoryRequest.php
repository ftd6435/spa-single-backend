<?php

namespace App\Modules\Settings\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'libelle' => ['required', 'string', 'min:2', 'max:160', Rule::unique('categories', 'libelle')->ignore($this->route()->parameter('category'))],
            'description' => ['nullable', 'string', 'min:2']
        ];
    }
}
