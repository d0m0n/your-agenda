<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StorageUsageBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_badge_is_hidden_when_usage_is_well_under_the_threshold(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('容量 ');
    }

    public function test_badge_appears_once_usage_crosses_80_percent(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();

        $image = UploadedFile::fake()->image('big.png', 1600, 1600);
        $bytes = filesize($image->getRealPath());

        // Quota just small enough that this one upload alone crosses 80%.
        $user->update(['storage_quota_bytes' => (int) ($bytes / 0.85)]);

        $this->actingAs($user)->post(route('materials.store'), [
            'title' => '資料', 'file' => $image,
        ])->assertSessionHasNoErrors();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('容量');
        $response->assertSee(route('settings.edit'), false);
    }

    public function test_observer_never_sees_the_storage_badge(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $observer = User::factory()->for($organization, 'organization')->observer()->create();

        $image = UploadedFile::fake()->image('big.png', 1600, 1600);
        $bytes = filesize($image->getRealPath());
        $user->update(['storage_quota_bytes' => (int) ($bytes / 0.85)]);

        $this->actingAs($user)->post(route('materials.store'), [
            'title' => '資料', 'file' => $image,
        ])->assertSessionHasNoErrors();

        $response = $this->actingAs($observer)->get(route('meetings.index'));

        $response->assertOk();
        $response->assertDontSee('容量 ');
    }
}
