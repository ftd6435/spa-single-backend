<?php

namespace App\Modules\Website\Resources;

use App\Modules\Administration\Resources\UserResource;
use App\Traits\ResourceHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class PartnerResource extends JsonResource
{
    use ResourceHelpers;

    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->attribute('id'),
            'name' => $this->attribute('name'),
            'acronym' => $this->attribute('acronym'),
            'domain' => $this->attribute('domain'),
            'description' => $this->attribute('description'),
            'logo_path' => $this->attribute('logo_path'),
            'logo_url' => $this->when($this->hasVisibleAttribute('logo_path'), fn () => $this->logo_url),
            'status' => $this->attribute('status'),
            'created_by' => UserResource::make($this->whenLoaded('createdBy')),
            'updated_by' => UserResource::make($this->whenLoaded('updatedBy')),
            'created_at' => $this->dateAttribute('created_at'),
            'updated_at' => $this->dateAttribute('updated_at'),
        ];
    }
}
