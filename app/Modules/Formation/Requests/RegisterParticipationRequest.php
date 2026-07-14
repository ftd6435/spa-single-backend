<?php

namespace App\Modules\Formation\Requests;

use App\Modules\Formation\Models\Participant;
use Illuminate\Foundation\Http\FormRequest;

class RegisterParticipationRequest extends FormRequest
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
            'nom' => ['required', 'string', 'min:2', 'max:160'],
            'prenom' => ['required', 'string', 'min:2', 'max:160'],
            'telephone' => ['required', 'string', 'min:6', 'max:30', 'regex:/^\+?[0-9]+$/'],
            'adresse' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ];
    }
}
