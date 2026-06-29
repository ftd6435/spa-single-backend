<?php

namespace App\Modules\Jobs\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobOpeningResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'skills' => $this->skills,
            'image' => $this->image,
            'closing_date' => $this->closing_date?->format('Y-m-d'),
            'is_active' => $this->is_active,

            'applications' => JobApplicationResource::collection(
                $this->whenLoaded('applications')
            ),

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
