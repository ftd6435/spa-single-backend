<?php

namespace App\Modules\Offer\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validation des données pour la création et la modification d'une offre
class OfferRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // offer_type_id doit pointer vers un type existant en base
            'offer_type_id' => ['required', 'integer', 'exists:offer_types,id'],
            'plan'          => ['required', 'string', 'min:2', 'max:160'],
            'price'         => ['nullable', 'numeric', 'min:0'],
            // features est un tableau de chaînes de caractères (liste des fonctionnalités)
            'features'      => ['nullable', 'array'],
            'features.*'    => ['string', 'min:1'],
            'is_popular'    => ['boolean'],
        ];
    }
}
