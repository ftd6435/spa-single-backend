<?php

namespace App\Modules\Jobs\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobApplicationResource extends JsonResource
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
            'job_opening_id' => $this->job_opening_id,

            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'email' => $this->email,
            'phone' => $this->phone,

            'cv_file' => $this->cv_file,
            'drive_link' => $this->drive_link,

            // Boolean pour le frontend
            'status' => $this->status === 'accepted',

            // Vrai statut métier
            'application_status' => $this->status,

            'job_opening' => new JobOpeningResource($this->whenLoaded('jobOpening')),

            'processes' => JobApplicationProcessResource::collection(
                $this->whenLoaded('processes')
            ),

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
