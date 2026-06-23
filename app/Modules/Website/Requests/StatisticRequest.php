<?php

namespace App\Modules\Website\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatisticRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'label' => ['required', 'string', 'min:2', 'max:160'],
            'value' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:20'],
        ];
    }
}