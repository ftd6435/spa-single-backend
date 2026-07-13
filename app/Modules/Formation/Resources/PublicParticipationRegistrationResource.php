<?php

namespace App\Modules\Formation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicParticipationRegistrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'formation_id' => $this->formation_id,
            'status' => $this->status->value,
            'created_at' => $this->created_at?->format('d-m-Y H:i:s'),
        ];
    }
}
