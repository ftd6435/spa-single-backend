<?php

namespace App\Modules\Website\Resources;

use App\Modules\Administration\Resources\UserResource;
use App\Traits\ResourceHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class TestimonialResource extends JsonResource
{
    use ResourceHelpers;

    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->attribute('id'),
            'project_id' => $this->attribute('project_id'),
            'client_id' => $this->attribute('client_id'),
            'content' => $this->attribute('content'),
            'status' => $this->attribute('status'),
            'client' => ClientResource::make($this->whenLoaded('client')),
            'project' => ProjectResource::make($this->whenLoaded('project')),
            'created_by' => UserResource::make($this->whenLoaded('createdBy')),
            'updated_by' => UserResource::make($this->whenLoaded('updatedBy')),
            'created_at' => $this->dateAttribute('created_at'),
            'updated_at' => $this->dateAttribute('updated_at'),
        ];
    }
}
