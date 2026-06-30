<?php

namespace App\Modules\Jobs\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'job_opening_id' => ['required', 'exists:job_openings,id'],

            'last_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                Rule::unique('job_applications', 'email')
                    ->where('job_opening_id', $this->job_opening_id),
            ],

            'phone' => ['nullable', 'string', 'max:50'],

            'cv_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'drive_link' => ['nullable', 'url'],
        ];
    }
}
