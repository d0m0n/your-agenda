<?php

namespace Tests\Feature;

use App\Models\AgendaItem;
use App\Models\Material;
use App\Models\Meeting;
use App\Models\Organization;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaItemMaterialLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_agenda_item_can_be_linked_to_an_organization_wide_material(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $material = Material::factory()->for($organization, 'organization')->for($user)->create(['title' => '会員規約']);

        $response = $this->actingAs($user)->post(route('agenda-items.store', $meeting), [
            'title' => '規約確認',
            'agenda_link' => 'material:'.$material->id,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('agenda_items', [
            'meeting_id' => $meeting->id,
            'title' => '規約確認',
            'material_id' => $material->id,
            'site_id' => null,
        ]);
    }

    public function test_agenda_item_can_still_be_linked_to_a_meeting_scoped_site(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $site = Site::factory()->for($organization, 'organization')->for($user)->create(['meeting_id' => $meeting->id]);

        $response = $this->actingAs($user)->post(route('agenda-items.store', $meeting), [
            'title' => '議案確認',
            'agenda_link' => 'site:'.$site->id,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('agenda_items', [
            'meeting_id' => $meeting->id,
            'title' => '議案確認',
            'site_id' => $site->id,
            'material_id' => null,
        ]);
    }

    public function test_selecting_material_link_and_editing_updates_the_stored_link(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $site = Site::factory()->for($organization, 'organization')->for($user)->create(['meeting_id' => $meeting->id]);
        $material = Material::factory()->for($organization, 'organization')->for($user)->create();

        $item = AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '議題', 'site_id' => $site->id]);

        $this->actingAs($user)->put(route('agenda-items.update', [$meeting, $item]), [
            'title' => '議題',
            'agenda_link' => 'material:'.$material->id,
        ])->assertSessionHasNoErrors();

        $item->refresh();
        $this->assertSame($material->id, $item->material_id);
        $this->assertNull($item->site_id);
    }

    public function test_meeting_show_page_links_to_the_materials_download_route(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $material = Material::factory()->for($organization, 'organization')->for($user)->create(['title' => '資料PDF']);
        AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '資料確認', 'material_id' => $material->id]);

        $response = $this->actingAs($user)->get(route('meetings.show', $meeting));

        $response->assertOk();
        $response->assertSee(route('materials.download', $material), false);
    }

    public function test_meeting_edit_page_offers_both_sites_and_materials_in_the_link_dropdown(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $site = Site::factory()->for($organization, 'organization')->for($user)->create(['meeting_id' => $meeting->id, 'title' => '会議専用議案']);
        $material = Material::factory()->for($organization, 'organization')->for($user)->create(['title' => '組織共有資料']);

        $response = $this->actingAs($user)->get(route('meetings.edit', $meeting));

        $response->assertOk();
        $response->assertSee('議案データのリンク');
        $response->assertSee('議案ファイル(この会議)');
        $response->assertSee('資料置き場(組織共有)');
        $response->assertSee($site->title);
        $response->assertSee($material->title);
    }

    public function test_copying_agenda_items_carries_over_the_material_link_but_not_the_site_link(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $sourceMeeting = Meeting::factory()->for($organization, 'organization')->create();
        $targetMeeting = Meeting::factory()->for($organization, 'organization')->create();

        $site = Site::factory()->for($organization, 'organization')->for($user)->create(['meeting_id' => $sourceMeeting->id]);
        $material = Material::factory()->for($organization, 'organization')->for($user)->create(['title' => '共有資料']);

        $item = AgendaItem::create([
            'meeting_id' => $sourceMeeting->id, 'order' => 1, 'title' => '議題A',
            'site_id' => $site->id, 'material_id' => $material->id,
        ]);

        $this->actingAs($user)->post(route('agenda-items.copy', $targetMeeting), [
            'source_meeting_id' => $sourceMeeting->id,
            'item_ids' => [$item->id],
        ])->assertSessionHasNoErrors();

        $copied = $targetMeeting->fresh()->topLevelAgendaItems->firstWhere('title', '議題A');
        $this->assertNotNull($copied);
        $this->assertSame($material->id, $copied->material_id, 'material links are shared org-wide, so they carry over');
        $this->assertNull($copied->site_id, 'site links stay meeting-specific and are not copied');
    }
}
