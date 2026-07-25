<?php

namespace Tests\Feature;

use App\Models\Meeting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

/**
 * 次第表示画面(meetings.show)にも、会議一覧画面と同じ個別ダウンロードの
 * リンク(meetings.export)を設けている。
 */
class MeetingShowDownloadLinkTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_general_user_sees_a_download_link_on_the_agenda_screen(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->get(route('meetings.show', $meeting))
            ->assertOk()
            ->assertSee(route('meetings.export', $meeting), false);
    }

    public function test_observer_does_not_see_a_download_link_on_the_agenda_screen(): void
    {
        [$organization, , $observer] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $this->actingAs($observer)->get(route('meetings.show', $meeting))
            ->assertOk()
            ->assertDontSee(route('meetings.export', $meeting), false);
    }
}
