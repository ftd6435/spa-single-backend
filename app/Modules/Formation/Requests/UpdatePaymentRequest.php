<?php

namespace App\Modules\Formation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'montant' => ['sometimes', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999999.99'],
            'methode_paiement' => ['sometimes', 'string', 'min:2', 'max:100'],
            'date_paiement' => ['sometimes', 'date'],
            'commentaire' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}
