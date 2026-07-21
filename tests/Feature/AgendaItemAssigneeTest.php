<?php

namespace Tests\Feature;

use App\Models\AgendaItem;
use App\Models\Meeting;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaItemAssigneeTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignee_label_shows_position_before_member_name(): void
    {
        $organization = Organization::factory()->create();
        $position = Position::factory()->for($organization, 'organization')->create(['name' => '理事長']);
        $member = Member::factory()->for($organization, 'organization')->create(['name' => '山田太郎', 'position_id' => $position->id]);
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $agendaItem = AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '議題', 'member_id' => $member->id]);

        $this->assertSame('理事長 山田太郎', $agendaItem->assigneeLabel());
    }

    public function test_assignee_label_shows_member_name_alone_when_no_position(): void
    {
        $organization = Organization::factory()->create();
        $member = Member::factory()->for($organization, 'organization')->create(['name' => '鈴木花子']);
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $agendaItem = AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '議題', 'member_id' => $member->id]);

        $this->assertSame('鈴木花子', $agendaItem->assigneeLabel());
    }

    public function test_assignee_label_falls_back_to_free_typed_name(): void
    {
        $organization = Organization::factory()->create();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $agendaItem = AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '議題', 'assignee_name' => '外部講師 佐藤様']);

        $this->assertSame('外部講師 佐藤様', $agendaItem->assigneeLabel());
    }

    public function test_manually_typed_assignee_name_can_be_saved_without_a_member(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $response = $this->actingAs($user)->post(route('agenda-items.store', $meeting), [
            'title' => 'ゲスト講演',
            'assignee_name' => '外部講師 佐藤様',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('agenda_items', [
            'meeting_id' => $meeting->id,
            'title' => 'ゲスト講演',
            'member_id' => null,
            'assignee_name' => '外部講師 佐藤様',
        ]);
    }

    public function test_selecting_a_member_takes_precedence_over_a_stray_free_typed_name(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $member = Member::factory()->for($organization, 'organization')->create(['name' => '山田太郎']);
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $response = $this->actingAs($user)->post(route('agenda-items.store', $meeting), [
            'title' => '議題',
            'member_id' => $member->id,
            'assignee_name' => 'この値は無視されるはず',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('agenda_items', [
            'meeting_id' => $meeting->id,
            'member_id' => $member->id,
            'assignee_name' => null,
        ]);
    }
}
