<?php

namespace App\Modules\Formation\Resources;

use App\Modules\Administration\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminFormationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'formation_category_id' => $this->formation_category_id,
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
            'is_active' => $this->is_active,
            'thumbnail_url' => $this->thumbnail_url,
            'category' => AdminFormationCategoryResource::make($this->whenLoaded('category')),
            'created_by' => UserResource::make($this->whenLoaded('createdBy')),
            'updated_by' => UserResource::make($this->whenLoaded('updatedBy')),
            'created_at' => $this->created_at?->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at?->format('d-m-Y H:i:s'),
            'deleted_at' => $this->deleted_at?->format('d-m-Y H:i:s'),
        ];
    }
}
