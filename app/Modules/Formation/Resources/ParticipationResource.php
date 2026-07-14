<?php

namespace App\Modules\Formation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParticipationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'formation_id' => $this->formation_id,
            'participant_id' => $this->participant_id,
            'frais_inscription_requis' => $this->frais_inscription_requis,
            'frais_inscription_paye' => $this->frais_inscription_paye,
            'status' => $this->status->value,
            'formation' => PublicFormationResource::make($this->whenLoaded('formation')),
            'participant' => ParticipantResource::make($this->whenLoaded('participant')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at?->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at?->format('d-m-Y H:i:s'),
            'deleted_at' => $this->deleted_at?->format('d-m-Y H:i:s'),
        ];
    }
}
