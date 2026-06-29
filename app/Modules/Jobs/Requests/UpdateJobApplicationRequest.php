<?php

namespace App\Modules\Jobs\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJobApplicationRequest extends FormRequest
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
            'job_opening_id' => ['sometimes', 'exists:job_openings,id'],

            'last_name' => ['sometimes', 'string', 'max:255'],
            'first_name' => ['sometimes', 'string', 'max:255'],

            'email' => [
                'sometimes',
                'email',
                Rule::unique('job_applications', 'email')
                    ->where('job_opening_id', $this->job_opening_id)
                    ->ignore($applicationId),
            ],

            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'cv_file' => ['sometimes', 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'drive_link' => ['sometimes', 'nullable', 'url'],

            'status' => ['sometimes', 'in:pending,reviewed,accepted,rejected'],
        ];
    }
}
