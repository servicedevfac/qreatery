<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\RestaurantStatus;
use App\SubscriptionPaymentStatus;
use App\SubscriptionStatus;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $now = now();

        return response()->json([
            'data' => [
                'restaurants' => [
                    'total' => Restaurant::query()->count(),
                    'active' => Restaurant::query()->where('status', RestaurantStatus::Active)->count(),
                    'suspended' => Restaurant::query()->where('status', RestaurantStatus::Suspended)->count(),
                ],
                'subscriptions' => [
                    'active' => Subscription::query()->where('status', SubscriptionStatus::Active)->count(),
                    'grace_period' => Subscription::query()->where('status', SubscriptionStatus::GracePeriod)->count(),
                    'expired' => Subscription::query()->where('status', SubscriptionStatus::Expired)->count(),
                    'expiring_within_7_days' => Subscription::query()
                        ->whereIn('status', [SubscriptionStatus::Trialing, SubscriptionStatus::Active, SubscriptionStatus::GracePeriod])
                        ->whereBetween('ends_at', [$now, $now->copy()->addDays(7)])
                        ->count(),
                ],
                'subscription_revenue' => [
                    'total_minor' => SubscriptionPayment::query()
                        ->where('status', SubscriptionPaymentStatus::Succeeded)
                        ->sum('amount_minor'),
                    'current_month_minor' => SubscriptionPayment::query()
                        ->where('status', SubscriptionPaymentStatus::Succeeded)
                        ->whereBetween('paid_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
                        ->sum('amount_minor'),
                ],
            ],
        ]);
    }
}
