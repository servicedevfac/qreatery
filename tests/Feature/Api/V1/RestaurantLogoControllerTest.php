<?php

namespace Tests\Feature\Api\V1;

use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RestaurantLogoControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_upload_a_logo_and_old_logo_is_deleted(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('restaurants/old-logo.png', 'old');
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create(['logo_path' => 'restaurants/old-logo.png']);
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();

        $response = $this->actingAs($owner)->postJson("/api/v1/restaurants/{$restaurant->id}/logo", [
            'logo' => $this->fakePng(),
        ]);

        $response->assertOk();
        $path = $restaurant->fresh()->logo_path;
        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertMissing('restaurants/old-logo.png');
    }

    public function test_non_member_cannot_upload_a_logo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $this->actingAs($user)->postJson("/api/v1/restaurants/{$restaurant->id}/logo", [
            'logo' => $this->fakePng(),
        ])->assertNotFound();
    }

    private function fakePng(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'logo.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true)
        );
    }
}
