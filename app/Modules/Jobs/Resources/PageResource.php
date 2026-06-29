<?php

namespace App\Modules\Jobs\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'link' => $this->link,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'heroes' => HeroResource::collection($this->whenLoaded('heroes')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
