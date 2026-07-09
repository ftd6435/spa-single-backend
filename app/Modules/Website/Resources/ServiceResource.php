<?php

namespace App\Modules\Website\Resources;

use App\Modules\Administration\Resources\UserResource;
use App\Modules\Settings\Resources\TagResource;
use App\Traits\ResourceHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class ServiceResource extends JsonResource
{
    use ResourceHelpers;

    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->attribute('id'),
            'icon' => $this->attribute('icon'),
            'image_path' => $this->attribute('image_path'),
            'image_url' => $this->when($this->hasVisibleAttribute('image_path'), fn () => $this->image_url),
            'title' => $this->attribute('title'),
            'short_description' => $this->attribute('short_description'),
            'description' => $this->attribute('description'),
            'benefits' => $this->attribute('benefits'),
            'status' => $this->attribute('status'),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_by' => UserResource::make($this->whenLoaded('createdBy')),
            'updated_by' => UserResource::make($this->whenLoaded('updatedBy')),
            'created_at' => $this->dateAttribute('created_at'),
            'updated_at' => $this->dateAttribute('updated_at'),
        ];
    }
}
