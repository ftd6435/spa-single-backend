<?php

namespace App\Modules\Formation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFormationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'formation_category_id' => [
                'required',
                'integer',
                Rule::exists('formation_categories', 'id')->whereNull('deleted_at'),
            ],
            'libelle' => ['required', 'string', 'min:2', 'max:200'],
            'short_description' => ['nullable', 'string'],
            'description' => ['required', 'string', 'min:2'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'nombre_places' => ['required', 'integer', 'min:1'],
            'lieu_formation' => ['required', 'string', 'min:2', 'max:255'],
            'date_fin_inscription' => ['required', 'date'],
            'frais_inscription' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'frais_formation' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'thumbnail' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'draft_token' => ['nullable', 'uuid'],
        ];
    }
}
