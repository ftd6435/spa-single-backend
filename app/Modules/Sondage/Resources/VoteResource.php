<?php

namespace App\Modules\Sondage\Resources;

use App\Traits\ResourceHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class VoteResource extends JsonResource
{
    use ResourceHelpers;

    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->attribute('id'),
            'reference'       => $this->attribute('reference'),
            'votant'          => VotantResource::make($this->whenLoaded('votant')),
            'init_sondage_id' => $this->attribute('init_sondage_id'),
            'scenario'        => $this->attribute('scenario'),
            'is_winner'       => $this->attribute('is_winner'),
            'created_at'      => $this->dateAttribute('created_at'),
        ];
    }
}
