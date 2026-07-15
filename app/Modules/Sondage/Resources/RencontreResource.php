<?php

namespace App\Modules\Sondage\Resources;

use App\Traits\ResourceHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class RencontreResource extends JsonResource
{
    use ResourceHelpers;

    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->attribute('id'),
            'home_team'       => EquipeResource::make($this->whenLoaded('homeTeam')),
            'away_team'       => EquipeResource::make($this->whenLoaded('awayTeam')),
            'winner'          => EquipeResource::make($this->whenLoaded('winner')),
            'init_sondage_id' => $this->attribute('init_sondage_id'),
            'phase'           => $this->attribute('phase'),
            'date_rencontre'  => $this->attribute('date_rencontre'),
            'heure_rencontre' => $this->attribute('heure_rencontre'),
            'final_score'     => $this->attribute('final_score'),
            'is_active'       => $this->attribute('is_active'),
            'created_at'      => $this->dateAttribute('created_at'),
            'updated_at'      => $this->dateAttribute('updated_at'),
        ];
    }
}
