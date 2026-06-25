<?php

namespace App\Modules\Website\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VisionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $requiredOnCreate = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'title' => [$requiredOnCreate, 'string', 'min:2', 'max:160'],
            'description' => [$requiredOnCreate, 'string', 'min:2'],
            'author' => ['nullable', 'string', 'max:160'],
        ];
    }
}
