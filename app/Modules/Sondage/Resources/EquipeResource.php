<?php

namespace App\Modules\Sondage\Resources;

use App\Traits\ResourceHelpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class EquipeResource extends JsonResource
{
    use ResourceHelpers;

    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->attribute('id'),
            'libelle'     => $this->attribute('libelle'),
            'description' => $this->attribute('description'),
            'created_at'  => $this->dateAttribute('created_at'),
            'updated_at'  => $this->dateAttribute('updated_at'),
        ];
    }
}
