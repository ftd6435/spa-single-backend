<?php

namespace App\Modules\Jobs\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],

            'estimated_budget' => ['sometimes', 'nullable', 'string', 'max:255'],
            'expected_deadline' => ['sometimes', 'nullable', 'string', 'max:255'],

            'full_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email'],
            'phone' => ['sometimes', 'string', 'max:50'],
            'company' => ['sometimes', 'nullable', 'string', 'max:255'],

            'status' => ['sometimes', 'in:pending,in_progress,approved,rejected'],
        ];
    }
}
