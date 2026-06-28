<?php

namespace App\Modules\Offer\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class OfferResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'plan'       => $this->plan,
            'price'      => $this->price,
            'features'   => $this->features,
            'is_popular' => $this->is_popular,
            'offer_type' => new OfferTypeResource($this->whenLoaded('offerType')),
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at->format('d-m-Y H:i:s'),
        ];
    }
}
