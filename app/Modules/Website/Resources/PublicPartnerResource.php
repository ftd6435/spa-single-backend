<?php

namespace App\Modules\Website\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class PublicPartnerResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'acronym' => $this->acronym,
            'domain' => $this->domain,
            'description' => $this->description,
            'logo_url' => $this->logo_url,
        ];
    }
}
