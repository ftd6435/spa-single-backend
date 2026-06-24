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
        return [
            'title' => ['required', 'string', 'min:2', 'max:160'],
            'description' => ['required', 'string', 'min:2'],
            'author' => ['nullable', 'string', 'max:160'],
        ];
    }
}