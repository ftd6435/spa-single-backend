<?php

namespace App\Modules\Website\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceTagRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'tag_id' => [
                'required',
                'integer',
                'exists:tags,id',
                Rule::unique('service_tag', 'tag_id')
                    ->where('service_id', $this->input('service_id')),
            ],
        ];
    }
}