<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
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
            'category_id' => $this->category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price_minor' => $this->price_minor,
            'image_url' => $this->image_path === null ? null : Storage::disk('public')->url($this->image_path),
            'status' => $this->status,
            'is_available' => $this->is_available,
            'preparation_minutes' => $this->preparation_minutes,
            'position' => $this->position,
            'availability' => $this->availability,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'option_groups' => OptionGroupResource::collection($this->whenLoaded('optionGroups')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
