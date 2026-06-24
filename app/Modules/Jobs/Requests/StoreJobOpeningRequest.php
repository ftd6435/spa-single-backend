<?php

namespace App\Modules\Jobs\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreJobOpeningRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:job_openings,slug'],

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
