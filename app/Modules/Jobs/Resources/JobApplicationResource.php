<?php

namespace App\Modules\Jobs\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class JobApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_opening_id' => $this->job_opening_id,

            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'email' => $this->email,
            'phone' => $this->phone,

            'cv_file' => $this->cv_file_url,
            'drive_link' => $this->drive_link,

            'status' => $this->status === 'accepted',
            'application_status' => $this->status,

            'job_opening' => new JobOpeningResource($this->whenLoaded('jobOpening')),

            'processes' => JobApplicationProcessResource::collection(
                $this->whenLoaded('processes')
            ),

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
