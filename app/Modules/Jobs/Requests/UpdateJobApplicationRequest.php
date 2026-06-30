<?php

namespace App\Modules\Jobs\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
                    ->ignore($this->route('application')),
            ],

            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'cv_file' => ['sometimes', 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'drive_link' => ['sometimes', 'nullable', 'url'],

            'status' => ['sometimes', 'in:pending,reviewed,accepted,rejected'],
        ];
    }
}
