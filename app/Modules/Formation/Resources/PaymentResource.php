<?php

namespace App\Modules\Formation\Resources;

use App\Modules\Administration\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'participation_id' => $this->participation_id,
            'montant' => $this->montant,
            'methode_paiement' => $this->methode_paiement,
            'date_paiement' => $this->date_paiement?->format('Y-m-d'),
            'commentaire' => $this->commentaire,
            'created_by' => UserResource::make($this->whenLoaded('createdBy')),
            'updated_by' => UserResource::make($this->whenLoaded('updatedBy')),
            'created_at' => $this->created_at?->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at?->format('d-m-Y H:i:s'),
            'deleted_at' => $this->deleted_at?->format('d-m-Y H:i:s'),
        ];
    }
}
