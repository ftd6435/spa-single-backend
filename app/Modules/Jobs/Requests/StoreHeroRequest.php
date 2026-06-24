<?php

namespace App\Modules\Jobs\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreHeroRequest extends FormRequest
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
            'page_id' => ['required', 'exists:pages,id'],
            'title' => ['required', 'string', 'max:255'],
            'sub_description' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,pdf', 'max:10240'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
