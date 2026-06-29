<?php

namespace App\Modules\Blog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

// Formate les données d'un commentaire — on n'expose pas article_id ni created_by (données internes)
class CommentResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'content'    => $this->content,
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
        ];
    }
}
