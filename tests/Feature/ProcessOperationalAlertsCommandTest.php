<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\OrderDelayedNotification;
use App\Notifications\SubscriptionExpiringNotification;
use App\Notifications\SubscriptionStatusChangedNotification;
use App\SubscriptionStatus;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProcessOperationalAlertsCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_command_alerts_delayed_orders_only_once(): void
    {
        Notification::fake();
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create(['settings' => ['order_delay_minutes' => 30]]);
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $order = Order::factory()->for($restaurant)->create(['created_at' => now()->subMinutes(45)]);

        $this->artisan('alerts:process')->assertSuccessful();
        $this->artisan('alerts:process')->assertSuccessful();

        Notification::assertSentToTimes($owner, OrderDelayedNotification::class, 1);
        $this->assertNotNull($order->refresh()->late_notified_at);
    }

    public function test_command_alerts_expiring_subscription_and_transitions_expired_one(): void
    {
        Notification::fake();
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $expiring = Subscription::factory()->for($restaurant)->create([
            'status' => SubscriptionStatus::Active,
            'ends_at' => now()->addDays(5),
            'grace_ends_at' => now()->addDays(12),
        ]);
        $expired = Subscription::factory()->for($restaurant)->create([
            'status' => SubscriptionStatus::Active,
            'ends_at' => now()->subDay(),
            'grace_ends_at' => now()->addDays(3),
        ]);

        $this->artisan('alerts:process')->assertSuccessful();

        Notification::assertSentTo($owner, SubscriptionExpiringNotification::class);
        Notification::assertSentTo($owner, SubscriptionStatusChangedNotification::class);
        $this->assertNotNull($expiring->refresh()->expiry_notified_at);
        $this->assertSame(SubscriptionStatus::GracePeriod, $expired->refresh()->status);
        $this->assertDatabaseHas('subscription_status_histories', [
            'subscription_id' => $expired->id,
            'from_status' => SubscriptionStatus::Active->value,
            'to_status' => SubscriptionStatus::GracePeriod->value,
            'actor_id' => null,
        ]);
    }
}
