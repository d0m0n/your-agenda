<?php

namespace Tests\Feature;

use App\Models\AgendaItem;
use App\Models\Material;
use App\Models\Meeting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class MeetingPublicSharingTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_manager_can_enable_public_link(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $this->assertNull($meeting->public_token);

        $response = $this->actingAs($general)->post(route('meetings.public-link.enable', $meeting));

        $response->assertRedirect(route('meetings.show', $meeting));
        $this->assertNotNull($meeting->fresh()->public_token);
    }

    public function test_manager_can_disable_public_link(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $meeting->public_token = (string) \Illuminate\Support\Str::uuid();
        $meeting->save();

        $response = $this->actingAs($general)->delete(route('meetings.public-link.disable', $meeting));

        $response->assertRedirect(route('meetings.show', $meeting));
        $this->assertNull($meeting->fresh()->public_token);
    }

    public function test_observer_cannot_enable_public_link(): void
    {
        [$organization, , $observer] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $this->actingAs($observer)
            ->post(route('meetings.public-link.enable', $meeting))
            ->assertForbidden();

        $this->assertNull($meeting->fresh()->public_token);
    }

    public function test_guest_can_view_meeting_via_public_link_without_logging_in(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create(['name' => '7月定例会']);
        AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '開会宣言']);

        $this->actingAs($general)->post(route('meetings.public-link.enable', $meeting));
        $token = $meeting->fresh()->public_token;

        $response = $this->get(route('public.meetings.show', $token));

        $response->assertOk();
        $response->assertSee('7月定例会');
        $response->assertSee('開会宣言');
    }

    public function test_guest_gets_404_for_an_unpublished_or_invalid_token(): void
    {
        [$organization] = $this->createTenant();
        Meeting::factory()->for($organization, 'organization')->create();

        $this->get(route('public.meetings.show', 'not-a-real-token'))->assertNotFound();
    }

    public function test_public_meeting_page_never_shows_wifi_or_memo(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create([
            'wifi_ssid' => 'jc-guest-wifi',
            'wifi_password' => 'super-secret-password',
            'memo' => '内部限定の引き継ぎメモ',
        ]);

        $this->actingAs($general)->post(route('meetings.public-link.enable', $meeting));
        $token = $meeting->fresh()->public_token;

        $response = $this->get(route('public.meetings.show', $token));

        $response->assertOk();
        $response->assertDontSee('jc-guest-wifi');
        $response->assertDontSee('super-secret-password');
        $response->assertDontSee('内部限定の引き継ぎメモ');
    }

    public function test_guest_can_download_a_material_linked_to_the_shared_meeting(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $material = Material::factory()->for($organization, 'organization')->create();
        Storage::disk('local')->put($material->file_path, 'dummy contents');
        AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '資料議題', 'material_id' => $material->id]);

        $this->actingAs($general)->post(route('meetings.public-link.enable', $meeting));
        $token = $meeting->fresh()->public_token;

        $this->get(route('public.meetings.materials.download', ['meeting' => $token, 'material' => $material]))
            ->assertOk();
    }

    public function test_guest_cannot_download_a_material_not_linked_to_the_shared_meeting(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $unrelatedMaterial = Material::factory()->for($organization, 'organization')->create();
        Storage::disk('local')->put($unrelatedMaterial->file_path, 'dummy contents');

        $this->actingAs($general)->post(route('meetings.public-link.enable', $meeting));
        $token = $meeting->fresh()->public_token;

        $this->get(route('public.meetings.materials.download', ['meeting' => $token, 'material' => $unrelatedMaterial]))
            ->assertNotFound();
    }

    public function test_disabling_the_public_link_blocks_the_old_url(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->post(route('meetings.public-link.enable', $meeting));
        $token = $meeting->fresh()->public_token;

        $this->actingAs($general)->delete(route('meetings.public-link.disable', $meeting));

        $this->get(route('public.meetings.show', $token))->assertNotFound();
    }
}
