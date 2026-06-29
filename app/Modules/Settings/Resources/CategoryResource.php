<?php

namespace App\Modules\Settings\Resources;

use App\Modules\Administration\Resources\UserResource;
use App\Traits\ResourceHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class CategoryResource extends JsonResource
{
    use ResourceHelpers;

    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->attribute('id'),
            'libelle' => $this->attribute('libelle'),
            'description' => $this->attribute('description'),
            'status' => $this->attribute('status'),
            'created_by' => UserResource::make($this->whenLoaded('createdBy')),
            'updated_by' => UserResource::make($this->whenLoaded('updatedBy')),
            'created_at' => $this->dateAttribute('created_at'),
            'updated_at' => $this->dateAttribute('updated_at'),
        ];
    }
}
