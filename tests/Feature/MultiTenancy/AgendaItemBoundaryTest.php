<?php

namespace Tests\Feature\MultiTenancy;

use App\Models\AgendaItem;
use App\Models\Material;
use App\Models\Meeting;
use App\Models\Member;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class AgendaItemBoundaryTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_agenda_item_belonging_to_another_organizations_meeting_cannot_be_updated_through_own_meeting(): void
    {
        [$orgA, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $meetingA = Meeting::factory()->for($orgA, 'organization')->create();
        $meetingB = Meeting::factory()->for($orgB, 'organization')->create();
        $agendaItemB = AgendaItem::create(['meeting_id' => $meetingB->id, 'order' => 1, 'title' => '他組織の議題']);

        $this->actingAs($userA)
            ->put(route('agenda-items.update', [$meetingA, $agendaItemB]), ['title' => '書き換え'])
            ->assertNotFound();

        $this->assertSame('他組織の議題', $agendaItemB->fresh()->title);
    }

    public function test_agenda_item_routes_are_blocked_when_meeting_belongs_to_another_organization(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $meetingB = Meeting::factory()->for($orgB, 'organization')->create();
        $agendaItemB = AgendaItem::create(['meeting_id' => $meetingB->id, 'order' => 1, 'title' => '他組織の議題']);

        $this->actingAs($userA)
            ->put(route('agenda-items.update', [$meetingB, $agendaItemB]), ['title' => '書き換え'])
            ->assertNotFound();

        $this->actingAs($userA)
            ->delete(route('agenda-items.destroy', [$meetingB, $agendaItemB]))
            ->assertNotFound();

        $this->assertModelExists($agendaItemB);
    }

    public function test_agenda_item_cannot_be_linked_to_a_site_from_a_different_meeting(): void
    {
        [$orgA, $userA] = $this->createTenant();

        $meetingA = Meeting::factory()->for($orgA, 'organization')->create();
        $otherMeetingA = Meeting::factory()->for($orgA, 'organization')->create();
        $siteFromOtherMeeting = Site::factory()
            ->for($orgA, 'organization')
            ->for($userA)
            ->create(['meeting_id' => $otherMeetingA->id]);

        $response = $this->actingAs($userA)->post(route('agenda-items.store', $meetingA), [
            'title' => '議題',
            'agenda_link' => 'site:'.$siteFromOtherMeeting->id,
        ]);

        $response->assertSessionHasErrors('site_id');
        $this->assertDatabaseMissing('agenda_items', ['meeting_id' => $meetingA->id]);
    }

    public function test_agenda_item_cannot_be_linked_to_a_material_from_another_organization(): void
    {
        [$orgA, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $meetingA = Meeting::factory()->for($orgA, 'organization')->create();
        $materialB = Material::factory()->for($orgB, 'organization')->create();

        $response = $this->actingAs($userA)->post(route('agenda-items.store', $meetingA), [
            'title' => '議題',
            'agenda_link' => 'material:'.$materialB->id,
        ]);

        $response->assertSessionHasErrors('material_id');
        $this->assertDatabaseMissing('agenda_items', ['meeting_id' => $meetingA->id]);
    }

    public function test_agenda_item_cannot_be_assigned_a_member_from_another_organization(): void
    {
        [$orgA, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $meetingA = Meeting::factory()->for($orgA, 'organization')->create();
        $memberB = Member::factory()->for($orgB, 'organization')->create();

        $response = $this->actingAs($userA)->post(route('agenda-items.store', $meetingA), [
            'title' => '議題',
            'member_id' => $memberB->id,
        ]);

        $response->assertSessionHasErrors('member_id');
        $this->assertDatabaseMissing('agenda_items', ['meeting_id' => $meetingA->id]);
    }

    public function test_agenda_item_cannot_be_created_as_a_child_of_another_organizations_item(): void
    {
        [$orgA, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $meetingA = Meeting::factory()->for($orgA, 'organization')->create();
        $meetingB = Meeting::factory()->for($orgB, 'organization')->create();
        $parentB = AgendaItem::create(['meeting_id' => $meetingB->id, 'order' => 1, 'title' => '他組織の議題']);

        $response = $this->actingAs($userA)->post(route('agenda-items.store', $meetingA), [
            'title' => '子項目',
            'parent_id' => $parentB->id,
        ]);

        $response->assertSessionHasErrors('parent_id');
        $this->assertDatabaseMissing('agenda_items', ['meeting_id' => $meetingA->id]);
    }

    public function test_agenda_item_cannot_be_created_as_a_grandchild(): void
    {
        [$orgA, $userA] = $this->createTenant();

        $meetingA = Meeting::factory()->for($orgA, 'organization')->create();
        $parent = AgendaItem::create(['meeting_id' => $meetingA->id, 'order' => 1, 'title' => '親項目']);
        $child = AgendaItem::create(['meeting_id' => $meetingA->id, 'parent_id' => $parent->id, 'order' => 1, 'title' => '子項目']);

        $response = $this->actingAs($userA)->post(route('agenda-items.store', $meetingA), [
            'title' => '孫項目',
            'parent_id' => $child->id,
        ]);

        $response->assertSessionHasErrors('parent_id');
    }

    public function test_observer_cannot_manage_agenda_items(): void
    {
        [$orgA, , $observerA] = $this->createTenant();

        $meeting = Meeting::factory()->for($orgA, 'organization')->create();
        $agendaItem = AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '議題']);

        $this->actingAs($observerA)->post(route('agenda-items.store', $meeting), ['title' => '不正追加'])->assertForbidden();
        $this->actingAs($observerA)->put(route('agenda-items.update', [$meeting, $agendaItem]), ['title' => '不正更新'])->assertForbidden();
        $this->actingAs($observerA)->delete(route('agenda-items.destroy', [$meeting, $agendaItem]))->assertForbidden();
        $this->actingAs($observerA)->post(route('agenda-items.move-up', [$meeting, $agendaItem]))->assertForbidden();
        $this->actingAs($observerA)->post(route('agenda-items.move-down', [$meeting, $agendaItem]))->assertForbidden();

        $this->assertSame('議題', $agendaItem->fresh()->title);
    }
}
