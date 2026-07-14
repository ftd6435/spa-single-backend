<?php

namespace App\Modules\Formation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'montant' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999999.99'],
            'methode_paiement' => ['required', 'string', 'min:2', 'max:100'],
            'date_paiement' => ['required', 'date'],
            'commentaire' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
