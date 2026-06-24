<?php

namespace App\Modules\Jobs\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJobOpeningRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:255'],

            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('job_openings', 'slug')->ignore($jobOpeningId),
            ],

            'short_description' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:255'],

            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            'closing_date' => ['nullable', 'date'],

            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
