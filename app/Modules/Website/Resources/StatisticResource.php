<?php

namespace App\Modules\Website\Resources;

use App\Modules\Administration\Resources\UserResource;
use App\Traits\ResourceHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class StatisticResource extends JsonResource
{
    use ResourceHelpers;

    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->attribute('id'),
            'label' => $this->attribute('label'),
            'value' => $this->attribute('value'),
            'unit' => $this->attribute('unit'),
            'created_by' => UserResource::make($this->whenLoaded('createdBy')),
            'updated_by' => UserResource::make($this->whenLoaded('updatedBy')),
            'created_at' => $this->dateAttribute('created_at'),
            'updated_at' => $this->dateAttribute('updated_at'),
        ];
    }
}
