<?php

namespace App\Modules\Sondage\Resources;

use App\Traits\ResourceHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class InitSondageResource extends JsonResource
{
    use ResourceHelpers;

    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->attribute('id'),
            'competition'    => CompetitionResource::make($this->whenLoaded('competition')),
            'libelle'        => $this->attribute('libelle'),
            'description'    => $this->attribute('description'),
            'avantage'       => $this->attribute('avantage'),
            'heure_debut'    => $this->attribute('heure_debut'),
            'heure_fin'      => $this->attribute('heure_fin'),
            'niveau_vote'    => $this->attribute('niveau_vote'),
            'cadeaux'        => $this->attribute('cadeaux'),
            'image'          => $this->attribute('image'),
            'is_active'      => $this->attribute('is_active'),
            'created_at'     => $this->dateAttribute('created_at'),
            'updated_at'     => $this->dateAttribute('updated_at'),
        ];
    }
}
