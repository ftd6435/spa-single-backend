<?php

namespace App\Modules\Offer\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

// Formate les données d'une offre renvoyées au client (on n'expose pas created_by, updated_by)
class OfferResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'plan'       => $this->plan,
            'price'      => $this->price,
            // features est un tableau PHP (converti depuis JSON par le cast du model)
            'features'   => $this->features,
            'is_popular' => $this->is_popular,
            'status'     => $this->status,
            // whenLoaded évite une requête supplémentaire si la relation n'a pas été eager loadée
            'offer_type' => new OfferTypeResource($this->whenLoaded('offerType')),
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at->format('d-m-Y H:i:s'),
        ];
    }
}
