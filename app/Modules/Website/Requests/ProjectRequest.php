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
        $requiredOnCreate = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'category_id' => [$requiredOnCreate, 'integer', 'exists:categories,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'title' => [$requiredOnCreate, 'string', 'min:2', 'max:160'],
            'short_description' => [$requiredOnCreate, 'string', 'min:2', 'max:1000'],
            'description' => [$requiredOnCreate, 'string', 'min:2'],
            'demo_link' => ['nullable', 'url', 'max:255'],
        ];
    }
}
