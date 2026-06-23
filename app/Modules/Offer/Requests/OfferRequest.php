<?php

namespace App\Modules\Offer\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfferRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'offer_type_id' => ['required', 'integer', 'exists:offer_types,id'],
            'plan' => ['required', 'string', 'min:2', 'max:160'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'min:1'],
            'is_popular' => ['boolean'],
        ];
    }
}