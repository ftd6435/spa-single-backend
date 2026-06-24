<?php

namespace App\Modules\Website\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'icon' => ['nullable', 'string', 'max:255'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'min:2', 'max:160'],
            'short_description' => ['required', 'string', 'min:2'],
            'description' => ['required', 'string', 'min:2'],
            'benefits' => ['nullable', 'array'],
            'benefits.*' => ['string', 'min:2'],
        ];
    }
}