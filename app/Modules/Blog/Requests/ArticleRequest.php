<?php

namespace App\Modules\Blog\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validation des données pour la création et la modification d'un article
class ArticleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'             => ['required', 'string', 'min:2', 'max:200'],
            'short_description' => ['nullable', 'string'],
            'description'       => ['required', 'string', 'min:2'],
            // max:2048 = 2 Mo — limite la taille de l'image uploadée
            'cover'             => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            // tags est un tableau d'IDs, chaque ID doit exister dans la table tags
            'tags'              => ['nullable', 'array'],
            'tags.*'            => ['integer', 'exists:tags,id'],
        ];
    }
}
