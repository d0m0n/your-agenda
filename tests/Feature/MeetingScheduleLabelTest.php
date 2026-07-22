<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingScheduleLabelTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_label_uses_the_japanese_date_format_with_weekday(): void
    {
        $meeting = new Meeting(['held_at' => '2026-07-22 19:00:00']); // 2026-07-22 is a Wednesday

        $this->assertSame('2026年07月22日(水)', $meeting->heldAtDateLabel());
        $this->assertSame('2026年07月22日(水) 19:00', $meeting->scheduleLabel());
    }

    public function test_schedule_label_shows_end_time_only_when_ends_at_is_the_same_day(): void
    {
        $meeting = new Meeting([
            'held_at' => '2026-07-22 19:00:00',
            'ends_at' => '2026-07-22 21:00:00',
        ]);

        $this->assertSame('2026年07月22日(水) 19:00 〜 21:00', $meeting->scheduleLabel());
    }

    public function test_schedule_label_shows_full_end_date_when_ends_at_is_a_different_day(): void
    {
        $meeting = new Meeting([
            'held_at' => '2026-07-22 19:00:00',
            'ends_at' => '2026-07-23 09:00:00', // Thursday
        ]);

        $this->assertSame('2026年07月22日(水) 19:00 〜 2026年07月23日(木) 09:00', $meeting->scheduleLabel());
    }

    public function test_schedule_label_appears_on_the_meeting_index_page(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        Meeting::factory()->for($organization, 'organization')->create(['held_at' => '2026-07-22 19:00:00']);

        $response = $this->actingAs($user)->get(route('meetings.index'));

        $response->assertOk();
        $response->assertSee('2026年07月22日(水)');
    }
}
