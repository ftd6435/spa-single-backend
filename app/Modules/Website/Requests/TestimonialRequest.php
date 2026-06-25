<?php

namespace App\Modules\Website\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestimonialRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $requiredOnCreate = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'project_id' => [$requiredOnCreate, 'integer', 'exists:projects,id'],
            'client_id' => [$requiredOnCreate, 'integer', 'exists:clients,id'],
            'content' => [$requiredOnCreate, 'string', 'min:2'],
        ];
    }
}
