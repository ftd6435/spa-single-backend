<?php

namespace App\Modules\Formation\Requests;

use App\Modules\Formation\Models\Participant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('telephone')) {
            $this->merge([
                'telephone' => Participant::normalizeTelephone((string) $this->input('telephone')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'nom' => ['sometimes', 'string', 'min:2', 'max:160'],
            'prenom' => ['sometimes', 'string', 'min:2', 'max:160'],
            'telephone' => [
                'sometimes',
                'string',
                'min:6',
                'max:30',
                'regex:/^\+?[0-9]+$/',
                Rule::unique('participants', 'telephone')->ignore($this->route('participant')),
            ],
            'adresse' => ['sometimes', 'nullable', 'string'],
            'avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ];
    }
}
