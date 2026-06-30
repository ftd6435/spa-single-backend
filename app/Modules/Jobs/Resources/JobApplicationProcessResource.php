<?php

namespace App\Modules\Jobs\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobApplicationProcessResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_application_id' => $this->job_application_id,

            'title' => $this->title,
            'description' => $this->description,

            'status' => $this->status,
            'is_completed' => $this->status === 'completed',

            'processed_by' => $this->processed_by,
            'processed_at' => $this->processed_at?->toDateTimeString(),

            'processor' => $this->whenLoaded('processor', function () {
                return [
                    'id' => $this->processor?->id,
                    'name' => $this->processor?->name,
                    'email' => $this->processor?->email,
                ];
            }),

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
