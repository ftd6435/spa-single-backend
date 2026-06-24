<?php

namespace App\Modules\Jobs\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHeroRequest extends FormRequest
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
            'page_id' => ['sometimes', 'exists:pages,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'sub_description' => ['sometimes', 'nullable', 'string'],
            'file' => ['sometimes', 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,pdf', 'max:10240'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
