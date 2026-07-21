<?php

namespace Tests\Feature;

use App\Models\AgendaItem;
use App\Models\Meeting;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaItemChildrenTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_child_item_can_be_created_under_a_top_level_item(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $parent = AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '11. 協議事項']);

        $response = $this->actingAs($user)->post(route('agenda-items.store', $meeting), [
            'title' => '●●の件',
            'parent_id' => $parent->id,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('agenda_items', [
            'meeting_id' => $meeting->id,
            'parent_id' => $parent->id,
            'title' => '●●の件',
        ]);
        $this->assertCount(1, $parent->fresh()->children);
    }

    public function test_child_order_numbering_is_independent_per_parent(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $parentA = AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '親A']);
        $parentB = AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 2, 'title' => '親B']);

        $this->actingAs($user)->post(route('agenda-items.store', $meeting), ['title' => 'A-1', 'parent_id' => $parentA->id]);
        $this->actingAs($user)->post(route('agenda-items.store', $meeting), ['title' => 'B-1', 'parent_id' => $parentB->id]);
        $this->actingAs($user)->post(route('agenda-items.store', $meeting), ['title' => 'A-2', 'parent_id' => $parentA->id]);

        $this->assertSame(['A-1', 'A-2'], $parentA->fresh()->children->pluck('title')->all());
        $this->assertSame(['B-1'], $parentB->fresh()->children->pluck('title')->all());
    }

    public function test_deleting_a_parent_item_deletes_its_children(): void
    {
        $organization = Organization::factory()->create();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $parent = AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '親']);
        $child = AgendaItem::create(['meeting_id' => $meeting->id, 'parent_id' => $parent->id, 'order' => 1, 'title' => '子']);

        $parent->delete();

        $this->assertModelMissing($child);
    }

    public function test_updating_a_child_item_does_not_change_its_parent(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $parent = AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '親']);
        $child = AgendaItem::create(['meeting_id' => $meeting->id, 'parent_id' => $parent->id, 'order' => 1, 'title' => '子']);

        $response = $this->actingAs($user)
            ->put(route('agenda-items.update', [$meeting, $child]), ['title' => '子(更新後)']);

        $response->assertSessionHasNoErrors();
        $this->assertSame($parent->id, $child->fresh()->parent_id);
        $this->assertSame('子(更新後)', $child->fresh()->title);
    }

    public function test_moving_a_child_item_only_reorders_within_its_siblings(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $parent = AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '親']);
        $childA = AgendaItem::create(['meeting_id' => $meeting->id, 'parent_id' => $parent->id, 'order' => 1, 'title' => '子A']);
        $childB = AgendaItem::create(['meeting_id' => $meeting->id, 'parent_id' => $parent->id, 'order' => 2, 'title' => '子B']);

        $this->actingAs($user)->post(route('agenda-items.move-down', [$meeting, $childA]));

        $this->assertSame(['子B', '子A'], $parent->fresh()->children->pluck('title')->all());
    }
}
