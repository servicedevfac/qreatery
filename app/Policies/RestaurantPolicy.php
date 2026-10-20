<?php

namespace App\Policies;

use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use App\RestaurantPermission;
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
            || $membership->hasPermission(RestaurantPermission::RestaurantUpdate)
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

    public function manageMenu(User $user, Restaurant $restaurant): Response
    {
        return $this->authorizeCapability($user, $restaurant, RestaurantPermission::MenuManage, 'gérer le menu');
    }

    public function manageTables(User $user, Restaurant $restaurant): Response
    {
        return $this->authorizeCapability($user, $restaurant, RestaurantPermission::TablesManage, 'gérer les tables');
    }

    public function manageQrCodes(User $user, Restaurant $restaurant): Response
    {
        return $this->authorizeCapability($user, $restaurant, RestaurantPermission::QrCodesManage, 'gérer les QR Codes');
    }

    public function viewOrders(User $user, Restaurant $restaurant): Response
    {
        $membership = $this->activeMembership($user, $restaurant);

        if (! $membership instanceof RestaurantMembership) {
            return Response::denyAsNotFound();
        }

        return in_array($membership->role, [RestaurantRole::Owner, RestaurantRole::Admin, RestaurantRole::Manager], true)
            || $membership->hasPermission(RestaurantPermission::OrdersView)
            || $membership->hasPermission(RestaurantPermission::OrdersManage)
            ? Response::allow()
            : Response::deny('Votre rôle ne permet pas de consulter les commandes.');
    }

    public function manageOrders(User $user, Restaurant $restaurant): Response
    {
        return $this->authorizeCapability($user, $restaurant, RestaurantPermission::OrdersManage, 'gérer les commandes');
    }

    public function managePayments(User $user, Restaurant $restaurant): Response
    {
        return $this->authorizeCapability($user, $restaurant, RestaurantPermission::PaymentsManage, 'gérer les paiements');
    }

    public function viewReports(User $user, Restaurant $restaurant): Response
    {
        $membership = $this->activeMembership($user, $restaurant);

        if (! $membership instanceof RestaurantMembership) {
            return Response::denyAsNotFound();
        }

        return in_array($membership->role, [RestaurantRole::Owner, RestaurantRole::Admin, RestaurantRole::Manager], true)
            || $membership->hasPermission(RestaurantPermission::ReportsView)
            ? Response::allow()
            : Response::deny('Votre rôle ne permet pas de consulter les rapports.');
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

    private function authorizeCapability(User $user, Restaurant $restaurant, RestaurantPermission $permission, string $action): Response
    {
        $membership = $this->activeMembership($user, $restaurant);

        if (! $membership instanceof RestaurantMembership) {
            return Response::denyAsNotFound();
        }

        return in_array($membership->role, [RestaurantRole::Owner, RestaurantRole::Admin], true)
            || $membership->hasPermission($permission)
            ? Response::allow()
            : Response::deny("Votre rôle ne permet pas de {$action}.");
    }
}
