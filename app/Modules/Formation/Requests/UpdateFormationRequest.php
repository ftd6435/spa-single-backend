<?php

namespace App\Modules\Formation\Requests;

use App\Modules\Formation\Models\Formation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateFormationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'formation_category_id' => [
                'sometimes',
                'integer',
                Rule::exists('formation_categories', 'id')->whereNull('deleted_at'),
            ],
            'libelle' => ['sometimes', 'string', 'min:2', 'max:200'],
            'short_description' => ['sometimes', 'nullable', 'string'],
            'description' => ['sometimes', 'string', 'min:2'],
            'date_debut' => ['sometimes', 'date'],
            'date_fin' => ['sometimes', 'date'],
            'nombre_places' => ['sometimes', 'integer', 'min:1'],
            'lieu_formation' => ['sometimes', 'string', 'min:2', 'max:255'],
            'date_fin_inscription' => ['sometimes', 'date'],
            'frais_inscription' => ['sometimes', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'frais_formation' => ['sometimes', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'thumbnail' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('date_debut') || $validator->errors()->has('date_fin')) {
                return;
            }

            $formation = Formation::find($this->route('formation'));
            if (! $formation) {
                return;
            }

            $start = Date::parse($this->input('date_debut', $formation->date_debut));
            $end = Date::parse($this->input('date_fin', $formation->date_fin));

            if ($end->lt($start)) {
                $validator->errors()->add('date_fin', 'La date de fin doit être postérieure ou égale à la date de début.');
            }
        });
    }
}
