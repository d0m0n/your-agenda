<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardUpcomingMeetingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_only_lists_meetings_from_today_onward_in_ascending_order(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();

        $past = Meeting::factory()->for($organization, 'organization')->create([
            'name' => '過去の会議', 'held_at' => now()->subDays(3),
        ]);
        $today = Meeting::factory()->for($organization, 'organization')->create([
            'name' => '本日の会議', 'held_at' => now()->addHour(),
        ]);
        $soon = Meeting::factory()->for($organization, 'organization')->create([
            'name' => '来週の会議', 'held_at' => now()->addWeek(),
        ]);
        $later = Meeting::factory()->for($organization, 'organization')->create([
            'name' => '来月の会議', 'held_at' => now()->addMonth(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('今後の会議予定');
        $response->assertDontSee($past->name);

        // Ascending by held_at: soonest upcoming meeting appears first.
        $content = $response->getContent();
        $todayPos = strpos($content, $today->name);
        $soonPos = strpos($content, $soon->name);
        $laterPos = strpos($content, $later->name);

        $this->assertNotFalse($todayPos);
        $this->assertNotFalse($soonPos);
        $this->assertNotFalse($laterPos);
        $this->assertTrue($todayPos < $soonPos && $soonPos < $laterPos);
    }

    public function test_dashboard_shows_no_schedule_message_when_there_are_no_upcoming_meetings(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();

        Meeting::factory()->for($organization, 'organization')->create([
            'name' => '過去の会議', 'held_at' => now()->subMonth(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('予定なし');
        $response->assertDontSee('過去の会議');
    }

    public function test_meeting_held_today_is_included(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();

        $meeting = Meeting::factory()->for($organization, 'organization')->create([
            'name' => '本日開催', 'held_at' => now()->startOfDay()->addMinutes(5),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee($meeting->name);
    }
}
