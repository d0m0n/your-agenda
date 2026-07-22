<?php

namespace Tests\Feature;

use App\Models\AgendaItem;
use App\Models\Meeting;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaItemCopyTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_top_level_items_and_their_children_are_copied_to_the_target_meeting(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $sourceMeeting = Meeting::factory()->for($organization, 'organization')->create();
        $targetMeeting = Meeting::factory()->for($organization, 'organization')->create();

        $parent = AgendaItem::create(['meeting_id' => $sourceMeeting->id, 'order' => 1, 'title' => '11. 協議事項', 'assignee_name' => '田中']);
        AgendaItem::create(['meeting_id' => $sourceMeeting->id, 'parent_id' => $parent->id, 'order' => 1, 'title' => '●●の件']);
        AgendaItem::create(['meeting_id' => $sourceMeeting->id, 'order' => 2, 'title' => '未選択の項目']);

        $existing = AgendaItem::create(['meeting_id' => $targetMeeting->id, 'order' => 1, 'title' => '既存の議題']);

        $response = $this->actingAs($user)->post(route('agenda-items.copy', $targetMeeting), [
            'source_meeting_id' => $sourceMeeting->id,
            'item_ids' => [$parent->id],
        ]);

        $response->assertRedirect(route('meetings.edit', $targetMeeting));
        $response->assertSessionHasNoErrors();

        $targetMeeting->refresh();
        $topLevel = $targetMeeting->topLevelAgendaItems;

        $this->assertCount(2, $topLevel);
        $copied = $topLevel->firstWhere('title', '11. 協議事項');
        $this->assertNotNull($copied);
        $this->assertSame('田中', $copied->assignee_name);
        $this->assertSame(2, $copied->order, 'copied item is appended after existing items');
        $this->assertCount(1, $copied->children);
        $this->assertSame('●●の件', $copied->children->first()->title);

        $this->assertDatabaseMissing('agenda_items', ['meeting_id' => $targetMeeting->id, 'title' => '未選択の項目']);
        $this->assertModelExists($existing);

        // Copying didn't mutate the source meeting.
        $this->assertCount(2, $sourceMeeting->fresh()->topLevelAgendaItems);
    }

    public function test_general_user_cannot_copy_agenda_items_from_another_organizations_meeting(): void
    {
        $organizationA = Organization::factory()->create();
        $userA = User::factory()->for($organizationA, 'organization')->create();
        $targetMeeting = Meeting::factory()->for($organizationA, 'organization')->create();

        $organizationB = Organization::factory()->create();
        $sourceMeetingB = Meeting::factory()->for($organizationB, 'organization')->create();
        $itemB = AgendaItem::create(['meeting_id' => $sourceMeetingB->id, 'order' => 1, 'title' => '他組織の議題']);

        $response = $this->actingAs($userA)->post(route('agenda-items.copy', $targetMeeting), [
            'source_meeting_id' => $sourceMeetingB->id,
            'item_ids' => [$itemB->id],
        ]);

        $response->assertSessionHasErrors('source_meeting_id');
        $this->assertDatabaseMissing('agenda_items', ['meeting_id' => $targetMeeting->id, 'title' => '他組織の議題']);
    }

    public function test_copy_section_is_hidden_when_no_other_meeting_has_agenda_items(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $meetingWithItems = Meeting::factory()->for($organization, 'organization')->create();
        $emptyMeeting = Meeting::factory()->for($organization, 'organization')->create();
        AgendaItem::create(['meeting_id' => $meetingWithItems->id, 'order' => 1, 'title' => '既存議題']);

        // Editing the only meeting that has items: there's nothing else to copy from.
        $response = $this->actingAs($user)->get(route('meetings.edit', $meetingWithItems));
        $response->assertOk();
        $response->assertDontSee('過去の次第からコピー');

        // Editing the empty meeting: the populated meeting is offered as a copy source.
        $response = $this->actingAs($user)->get(route('meetings.edit', $emptyMeeting));
        $response->assertOk();
        $response->assertSee('過去の次第からコピー');
        $response->assertSee($meetingWithItems->name);
    }
}
