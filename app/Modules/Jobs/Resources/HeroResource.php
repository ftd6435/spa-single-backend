<?php

namespace App\Modules\Jobs\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeroResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'page_id' => $this->page_id,
            'title' => $this->title,
            'sub_description' => $this->sub_description,
            'file' => $this->file_url,
            'is_active' => $this->is_active,
            'page' => new PageResource($this->whenLoaded('page')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
