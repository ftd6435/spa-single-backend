<?php

namespace App\Modules\Offer\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Validation des données pour la création et la modification d'un type d'offre
class OfferTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // ignore() permet d'exclure la ressource en cours lors d'une modification (évite le conflit unique sur elle-même)
            'name' => ['required', 'string', 'min:2', 'max:160', Rule::unique('offer_types', 'name')->ignore($this->route()->parameter('offer_type'))],
            'description' => ['nullable', 'string', 'min:2'],
        ];
    }
}