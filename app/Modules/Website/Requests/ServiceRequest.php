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
        $requiredOnCreate = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'icon' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'title' => [$requiredOnCreate, 'string', 'min:2', 'max:160'],
            'short_description' => [$requiredOnCreate, 'string', 'min:2', 'max:1000'],
            'description' => [$requiredOnCreate, 'string', 'min:2'],
            'benefits' => ['nullable', 'array'],
            'benefits.*' => ['string', 'min:2'],

            // tag_ids absent sur update : les tags existants ne changent pas.
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'distinct', 'exists:tags,id'],
        ];
    }
}
