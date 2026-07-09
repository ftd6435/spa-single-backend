<?php

namespace App\Modules\Website\Resources;

use App\Modules\Administration\Resources\UserResource;
use App\Traits\ResourceHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class VisionResource extends JsonResource
{
    use ResourceHelpers;

    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->attribute('id'),
            'title' => $this->attribute('title'),
            'description' => $this->attribute('description'),
            'author' => $this->attribute('author'),
            'status' => $this->attribute('status'),
            'created_by' => UserResource::make($this->whenLoaded('createdBy')),
            'updated_by' => UserResource::make($this->whenLoaded('updatedBy')),
            'created_at' => $this->dateAttribute('created_at'),
            'updated_at' => $this->dateAttribute('updated_at'),
        ];
    }
}
