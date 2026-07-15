<?php

namespace App\Modules\Sondage\Resources;

use App\Traits\ResourceHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class CompetitionEquipeResource extends JsonResource
{
    use ResourceHelpers;

    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->attribute('id'),
            'competition' => CompetitionResource::make($this->whenLoaded('competition')),
            'equipe'      => EquipeResource::make($this->whenLoaded('equipe')),
            'created_at'  => $this->dateAttribute('created_at'),
        ];
    }
}
