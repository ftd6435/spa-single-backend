<?php

namespace App\Modules\Website\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PartnerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $nameRule = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'name' => [$nameRule, 'string', 'min:2', 'max:160'],
            'acronym' => ['nullable', 'string', 'max:50'],
            'domain' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ];
    }
}