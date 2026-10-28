<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\CreateSubscriptionAction;
use App\Actions\RecordAuditLogAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRestaurantRequest;
use App\Http\Requests\UpdateAdminRestaurantStatusRequest;
use App\Http\Resources\RestaurantResource;
use App\Models\Restaurant;
use App\Models\User;
use App\RestaurantRole;
use App\SubscriptionStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $restaurants = Restaurant::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->toString().'%';
                $query->where(fn ($inner) => $inner->where('name', 'like', $search)->orWhere('slug', 'like', $search));
            })
            ->latest('id')
            ->paginate();

        return RestaurantResource::collection($restaurants);
    }

    public function store(
        StoreAdminRestaurantRequest $request,
        CreateSubscriptionAction $createSubscription,
        RecordAuditLogAction $recordAuditLog,
    ): JsonResponse|RedirectResponse {
        $validated = $request->validated();

        $restaurant = DB::transaction(function () use ($validated, $request, $createSubscription, $recordAuditLog): Restaurant {
            // 1. Déterminer l'utilisateur propriétaire
            $owner = null;
            if (! empty($validated['owner_email'])) {
                $owner = User::query()->firstOrNew(['email' => strtolower((string) $validated['owner_email'])]);
                if (! $owner->exists) {
                    $owner->name = $validated['owner_name'] ?? ($validated['name'].' Propriétaire');
                    $owner->password = Hash::make($validated['owner_password'] ?? 'password');
                    $owner->is_active = true;
                    $owner->email_verified_at = now();
                    $owner->forceFill(['is_platform_admin' => false]);
                    $owner->save();
                }
            } else {
                $owner = $request->user();
            }

            // 2. Génération ou validation du slug
            $slug = ! empty($validated['slug'])
                ? Str::slug($validated['slug'])
                : (Str::slug($validated['name']) ?: 'restaurant');

            if (Restaurant::query()->where('slug', $slug)->exists()) {
                $slug = $slug.'-'.Str::lower(Str::random(6));
            }

            // 3. Création du restaurant
            $restaurant = Restaurant::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'currency' => strtoupper((string) ($validated['currency'] ?? 'XOF')),
                'timezone' => $validated['timezone'] ?? 'Africa/Abidjan',
                'status' => $validated['status'] ?? 'active',
            ]);

            // 4. Rattachement du rôle propriétaire
            $restaurant->memberships()->create([
                'user_id' => $owner->id,
                'role' => RestaurantRole::Owner,
                'is_active' => true,
            ]);

            // 5. Initialisation éventuelle d'un abonnement SaaS
            if (! empty($validated['plan_id'])) {
                $createSubscription->handle($restaurant, $request->user(), [
                    'plan_id' => $validated['plan_id'],
                    'status' => SubscriptionStatus::Active->value,
                ]);
            }

            // 6. Journalisation d'audit
            $recordAuditLog->handle($request->user(), 'restaurant.created', $restaurant, $restaurant, [
                'name' => $restaurant->name,
                'slug' => $restaurant->slug,
                'status' => $restaurant->status->value,
                'owner_id' => $owner->id,
                'owner_email' => $owner->email,
                'plan_id' => $validated['plan_id'] ?? null,
            ]);

            return $restaurant;
        });

        if (! $request->expectsJson()) {
            return redirect('/?view=admin&section=restaurants')
                ->with('success', "L'établissement {$restaurant->name} a été créé avec succès.");
        }

        return (new RestaurantResource($restaurant))
            ->response()
            ->setStatusCode(201);
    }

    public function updateStatus(
        UpdateAdminRestaurantStatusRequest $request,
        Restaurant $restaurant,
        RecordAuditLogAction $recordAuditLog,
    ): RestaurantResource {
        $fromStatus = $restaurant->status->value;
        $restaurant->update(['status' => $request->validated('status')]);
        $recordAuditLog->handle($request->user(), 'restaurant.status_changed', $restaurant, $restaurant, [
            'from_status' => $fromStatus,
            'to_status' => $restaurant->refresh()->status->value,
        ]);

        return new RestaurantResource($restaurant);
    }
}
