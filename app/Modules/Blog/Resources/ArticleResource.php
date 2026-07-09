<?php

namespace App\Modules\Blog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

// Formate les données d'un article renvoyées au client
// Seuls les champs déclarés ici sont exposés dans la réponse JSON
class ArticleResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'short_description' => $this->short_description,
            'description'       => $this->description,
            // URL signée Cloudflare R2 (générée par l'accessor du model), pas le path brut
            'cover_url'         => $this->cover_url,
            'status'            => $this->status,
            // whenLoaded évite d'inclure la relation si elle n'a pas été chargée (eager load)
            'tags'              => $this->whenLoaded('tags'),
            'comments'          => CommentResource::collection($this->whenLoaded('comments')),
            // Opérateur ?-> : retourne null si createdBy/updatedBy est null (utilisateur supprimé)
            'created_by'        => $this->createdBy?->name,
            'updated_by'        => $this->updatedBy?->name,
            'created_at'        => $this->created_at->format('d-m-Y H:i:s'),
            'updated_at'        => $this->updated_at->format('d-m-Y H:i:s'),
        ];
    }
}
