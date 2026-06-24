<?php

namespace App\Modules\Website\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'first_name' => ['required', 'string', 'min:2', 'max:160'],
            'last_name' => ['required', 'string', 'min:2', 'max:160'],
            'job_title' => ['nullable', 'string', 'max:160'],
        ];
    }
}