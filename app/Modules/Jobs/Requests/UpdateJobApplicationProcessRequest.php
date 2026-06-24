<?php

namespace App\Modules\Jobs\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJobApplicationProcessRequest extends FormRequest
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
            'job_application_id' => ['sometimes', 'exists:job_applications,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'in:pending,in_progress,completed'],
            'processed_by' => ['sometimes', 'nullable', 'exists:users,id'],
            'processed_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
