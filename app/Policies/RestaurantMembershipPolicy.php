<?php

namespace App\Policies;

use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use App\RestaurantPermission;
use App\RestaurantRole;
use Illuminate\Auth\Access\Response;

class RestaurantMembershipPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Restaurant $restaurant): Response
    {
        $membership = $user->membershipFor($restaurant);

        if (! $membership instanceof RestaurantMembership) {
            return Response::denyAsNotFound();
        }

        return $this->canViewMembers($membership)
            ? Response::allow()
            : Response::deny('Votre rôle ne permet pas de consulter les membres.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RestaurantMembership $restaurantMembership): Response
    {
        return $this->viewAny($user, $restaurantMembership->restaurant);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Restaurant $restaurant): Response
    {
        return $this->manageResponse($user->membershipFor($restaurant));
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RestaurantMembership $restaurantMembership): Response
    {
        if ($restaurantMembership->role === RestaurantRole::Owner) {
            return Response::deny('Le propriétaire principal ne peut pas être modifié.');
        }

        $actorMembership = $user->membershipFor($restaurantMembership->restaurant);

        if ($actorMembership?->role !== RestaurantRole::Owner
            && $restaurantMembership->role === RestaurantRole::Admin) {
            return Response::deny('Seul le propriétaire peut gérer un administrateur.');
        }

        return $this->manageResponse($actorMembership);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RestaurantMembership $restaurantMembership): Response
    {
        return $this->update($user, $restaurantMembership);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, RestaurantMembership $restaurantMembership): Response
    {
        return $this->update($user, $restaurantMembership);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, RestaurantMembership $restaurantMembership): bool
    {
        return false;
    }

    private function canViewMembers(RestaurantMembership $membership): bool
    {
        return in_array($membership->role, [RestaurantRole::Owner, RestaurantRole::Admin], true)
            || $membership->hasPermission(RestaurantPermission::MembersView)
            || $membership->hasPermission(RestaurantPermission::MembersManage);
    }

    private function manageResponse(?RestaurantMembership $membership): Response
    {
        if (! $membership instanceof RestaurantMembership) {
            return Response::denyAsNotFound();
        }

        return in_array($membership->role, [RestaurantRole::Owner, RestaurantRole::Admin], true)
            || $membership->hasPermission(RestaurantPermission::MembersManage)
            ? Response::allow()
            : Response::deny('Votre rôle ne permet pas de gérer les membres.');
    }
}
