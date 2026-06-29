<?php

namespace App\Modules\Website\Resources;

use App\Modules\Administration\Resources\UserResource;
use App\Modules\Settings\Resources\CategoryResource;
use App\Traits\ResourceHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class ProjectResource extends JsonResource
{
    use ResourceHelpers;

    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->attribute('id'),
            'category_id' => $this->attribute('category_id'),
            'service_id' => $this->attribute('service_id'),
            'title' => $this->attribute('title'),
            'short_description' => $this->attribute('short_description'),
            'description' => $this->attribute('description'),
            'demo_link' => $this->attribute('demo_link'),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'service' => ServiceResource::make($this->whenLoaded('service')),
            'created_by' => UserResource::make($this->whenLoaded('createdBy')),
            'updated_by' => UserResource::make($this->whenLoaded('updatedBy')),
            'created_at' => $this->dateAttribute('created_at'),
            'updated_at' => $this->dateAttribute('updated_at'),
        ];
    }
}
