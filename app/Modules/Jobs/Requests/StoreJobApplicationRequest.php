<?php

namespace App\Modules\Jobs\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreJobApplicationRequest extends FormRequest
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

// Cette règle empêche une personne de postuler deux fois à la même offre avec le même email.