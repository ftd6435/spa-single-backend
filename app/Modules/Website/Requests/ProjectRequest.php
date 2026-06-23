<?php

namespace App\Modules\Website\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'title' => ['required', 'string', 'min:2', 'max:160'],
            'short_description' => ['required', 'string', 'min:2'],
            'description' => ['required', 'string', 'min:2'],
            'demo_link' => ['nullable', 'url', 'max:255'],
        ];
    }
}