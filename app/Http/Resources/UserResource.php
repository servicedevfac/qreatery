<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'is_active' => $this->is_active,
            'is_platform_admin' => (bool) $this->is_platform_admin,
            'redirect_url' => (function () {
                if ($this->is_platform_admin) {
                    return '/?view=admin';
                }
                $membership = $this->restaurantMemberships()->where('is_active', true)->with('restaurant')->first();
                if ($membership) {
                    $role = $membership->role?->value ?? (string) $membership->role;
                    $slug = $membership->restaurant?->slug ?? 'restaurant-demo';
                    $email = strtolower((string) $this->email);

                    if ($role === 'cashier') {
                        return "/?view=pos&restaurant={$slug}";
                    }
                    if ($role === 'staff') {
                        if (str_contains($email, 'cook') || str_contains($email, 'kds') || str_contains($email, 'cuisine')) {
                            return "/?view=kds&restaurant={$slug}";
                        }

                        return "/?view=restaurant&section=orders&restaurant={$slug}";
                    }

                    return "/?view=restaurant&restaurant={$slug}";
                }

                return '/?restaurant=restaurant-demo';
            })(),
            'last_login_at' => $this->last_login_at,
            'created_at' => $this->created_at,
        ];
    }
}
