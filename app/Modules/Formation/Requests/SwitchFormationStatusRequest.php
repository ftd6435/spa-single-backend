<?php

namespace App\Modules\Formation\Requests;

use App\Modules\Formation\Enums\FormationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SwitchFormationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(FormationStatus::class)],
        ];
    }
}
