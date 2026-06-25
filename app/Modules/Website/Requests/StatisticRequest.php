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
        $requiredOnCreate = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'label' => [$requiredOnCreate, 'string', 'min:2', 'max:160'],
            'value' => [$requiredOnCreate, 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:20'],
        ];
    }
}
