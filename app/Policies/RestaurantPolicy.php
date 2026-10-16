<?php

namespace App\Policies;

use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use App\RestaurantRole;
use Illuminate\Auth\Access\Response;

class RestaurantPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Restaurant $restaurant): Response
    {
        return $this->activeMembership($user, $restaurant) instanceof RestaurantMembership
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Restaurant $restaurant): Response
    {
        $membership = $this->activeMembership($user, $restaurant);

        if (! $membership instanceof RestaurantMembership) {
            return Response::denyAsNotFound();
        }

        return in_array($membership->role, [RestaurantRole::Owner, RestaurantRole::Admin], true)
            ? Response::allow()
            : Response::deny('Votre rôle ne permet pas de modifier ce restaurant.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Restaurant $restaurant): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Restaurant $restaurant): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Restaurant $restaurant): bool
    {
        return false;
    }

    private function activeMembership(User $user, Restaurant $restaurant): ?RestaurantMembership
    {
        return $restaurant->memberships()
            ->whereBelongsTo($user)
            ->where('is_active', true)
            ->first();
    }
}
