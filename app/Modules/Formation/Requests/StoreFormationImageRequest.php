<?php

namespace App\Modules\Formation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormationImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'upload' => ['required', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:2048'],
        ];
    }
}
