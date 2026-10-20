<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OptionGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'minimum_selections' => $this->minimum_selections,
            'maximum_selections' => $this->maximum_selections,
            'is_required' => $this->minimum_selections > 0,
            'is_active' => $this->is_active,
            'position' => $this->position,
            'options' => OptionResource::collection($this->whenLoaded('options')),
        ];
    }
}
