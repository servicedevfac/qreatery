<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PublicMenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'restaurant' => [
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'logo_url' => $this->logo_path === null ? null : Storage::disk('public')->url($this->logo_path),
                'currency' => $this->currency,
                'timezone' => $this->timezone,
                'settings' => $this->settings,
            ],
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
        ];
    }
}
