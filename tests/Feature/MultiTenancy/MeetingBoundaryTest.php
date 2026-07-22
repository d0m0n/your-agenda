<?php

namespace Tests\Feature\MultiTenancy;

use App\Models\Meeting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class MeetingBoundaryTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_general_user_cannot_view_edit_form_of_another_organizations_meeting(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $meetingB = Meeting::factory()->for($orgB, 'organization')->create();

        $this->actingAs($userA)
            ->get(route('meetings.edit', $meetingB))
            ->assertNotFound();
    }

    public function test_general_user_cannot_update_another_organizations_meeting(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $meetingB = Meeting::factory()->for($orgB, 'organization')->create(['name' => '他組織の例会']);

        $this->actingAs($userA)
            ->put(route('meetings.update', $meetingB), ['name' => '書き換え例会'])
            ->assertNotFound();

        $this->assertSame('他組織の例会', $meetingB->fresh()->name);
    }

    public function test_general_user_cannot_delete_another_organizations_meeting(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $meetingB = Meeting::factory()->for($orgB, 'organization')->create();

        $this->actingAs($userA)
            ->delete(route('meetings.destroy', $meetingB))
            ->assertNotFound();

        $this->assertModelExists($meetingB);
    }

    public function test_meeting_show_page_is_blocked_for_another_organizations_general_user(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $meetingB = Meeting::factory()->for($orgB, 'organization')->create();

        $this->actingAs($userA)
            ->get(route('meetings.show', $meetingB))
            ->assertNotFound();
    }

    public function test_meeting_show_page_is_blocked_for_another_organizations_observer(): void
    {
        [, , $observerA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $meetingB = Meeting::factory()->for($orgB, 'organization')->create();

        $this->actingAs($observerA)
            ->get(route('meetings.show', $meetingB))
            ->assertNotFound();
    }

    public function test_meeting_index_does_not_list_other_organizations_meetings(): void
    {
        [$orgA, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        Meeting::factory()->for($orgA, 'organization')->create(['name' => '自組織の例会']);
        Meeting::factory()->for($orgB, 'organization')->create(['name' => '他組織の例会']);

        $response = $this->actingAs($userA)->get(route('meetings.index'));

        $response->assertOk();
        $response->assertSee('自組織の例会');
        $response->assertDontSee('他組織の例会');
    }

    public function test_observer_cannot_access_meeting_management_routes(): void
    {
        [$orgA, , $observerA] = $this->createTenant();

        $meeting = Meeting::factory()->for($orgA, 'organization')->create();

        $this->actingAs($observerA)->get(route('meetings.create'))->assertForbidden();
        $this->actingAs($observerA)->get(route('meetings.edit', $meeting))->assertForbidden();
        $this->actingAs($observerA)->put(route('meetings.update', $meeting), ['name' => '不正更新'])->assertForbidden();
        $this->actingAs($observerA)->delete(route('meetings.destroy', $meeting))->assertForbidden();

        $this->assertModelExists($meeting);
    }

    public function test_observer_can_view_meeting_index_but_not_management_links(): void
    {
        [$orgA, , $observerA] = $this->createTenant();

        Meeting::factory()->for($orgA, 'organization')->create(['name' => '自組織の例会']);

        $response = $this->actingAs($observerA)->get(route('meetings.index'));

        $response->assertOk();
        $response->assertSee('自組織の例会');
        $response->assertDontSee(route('meetings.create'), false);
    }

    public function test_meeting_index_does_not_list_other_organizations_meetings_for_observer(): void
    {
        [$orgA, , $observerA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        Meeting::factory()->for($orgA, 'organization')->create(['name' => '自組織の例会']);
        Meeting::factory()->for($orgB, 'organization')->create(['name' => '他組織の例会']);

        $response = $this->actingAs($observerA)->get(route('meetings.index'));

        $response->assertOk();
        $response->assertSee('自組織の例会');
        $response->assertDontSee('他組織の例会');
    }
}
