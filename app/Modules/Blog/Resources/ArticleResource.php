<?php

namespace App\Modules\Blog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

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
            'cover_url'         => $this->cover_url,
            'tags'              => $this->whenLoaded('tags'),
            'comments'          => CommentResource::collection($this->whenLoaded('comments')),
            'created_by'        => $this->createdBy?->name,
            'updated_by'        => $this->updatedBy?->name,
            'created_at'        => $this->created_at->format('d-m-Y H:i:s'),
            'updated_at'        => $this->updated_at->format('d-m-Y H:i:s'),
        ];
    }
}
