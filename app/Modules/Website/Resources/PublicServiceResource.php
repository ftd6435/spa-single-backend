<?php

namespace App\Modules\Website\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class PublicServiceResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'icon' => $this->icon,
            'image_url' => $this->image_url,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'benefits' => $this->benefits,
        ];
    }
}
