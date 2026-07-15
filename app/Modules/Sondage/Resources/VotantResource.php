<?php

namespace App\Modules\Sondage\Resources;

use App\Traits\ResourceHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class VotantResource extends JsonResource
{
    use ResourceHelpers;

    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->attribute('id'),
            'name'       => $this->attribute('name'),
            'telephone'  => $this->attribute('telephone'),
            'created_at' => $this->dateAttribute('created_at'),
        ];
    }
}
