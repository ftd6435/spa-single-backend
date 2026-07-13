<?php

namespace App\Modules\Formation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicFormationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'libelle' => $this->libelle,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'date_debut' => $this->date_debut?->format('Y-m-d'),
            'date_fin' => $this->date_fin?->format('Y-m-d'),
            'date_fin_inscription' => $this->date_fin_inscription?->format('Y-m-d'),
            'nombre_places' => $this->nombre_places,
            'lieu_formation' => $this->lieu_formation,
            'frais_inscription' => $this->frais_inscription,
            'frais_formation' => $this->frais_formation,
            'status' => $this->status->value,
            'thumbnail_url' => $this->thumbnail_url,
            'category' => PublicFormationCategoryResource::make($this->whenLoaded('category')),
        ];
    }
}
