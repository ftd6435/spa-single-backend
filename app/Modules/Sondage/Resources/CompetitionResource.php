<?php

namespace App\Modules\Sondage\Resources;

use App\Traits\ResourceHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class CompetitionResource extends JsonResource
{
    use ResourceHelpers;

    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->attribute('id'),
            'libelle'     => $this->attribute('libelle'),
            'description' => $this->attribute('description'),
            'saison'      => $this->attribute('saison'),
            'is_active'   => $this->attribute('is_active'),
            'created_at'  => $this->dateAttribute('created_at'),
            'updated_at'  => $this->dateAttribute('updated_at'),
        ];
    }
}
