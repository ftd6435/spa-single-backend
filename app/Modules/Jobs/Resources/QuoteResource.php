<?php

namespace App\Modules\Jobs\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'project_name' => $this->project_name,
            'description' => $this->description,
            'estimated_budget' => $this->estimated_budget,
            'expected_deadline' => $this->expected_deadline,

            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,

            'status' => $this->status,
            'is_approved' => $this->status === 'approved',

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
