<?php

namespace App\Modules\Formation\Requests;

use App\Modules\Formation\Enums\ParticipationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SwitchParticipationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ParticipationStatus::class)],
        ];
    }
}
