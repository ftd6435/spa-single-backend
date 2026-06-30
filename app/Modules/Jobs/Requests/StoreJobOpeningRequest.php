<?php

namespace App\Modules\Jobs\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobOpeningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:255'],

            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            'closing_date' => ['nullable', 'date'],

            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
