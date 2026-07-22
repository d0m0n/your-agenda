<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_user_sees_onboarding_card_when_organization_has_no_members_or_meetings(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('ようこそ、「あなた次第」へ');
        $response->assertSee(route('members.create'), false);
        $response->assertSee(route('meetings.create'), false);
    }

    public function test_onboarding_card_is_hidden_once_a_member_exists(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        Member::factory()->for($organization, 'organization')->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('ようこそ、「あなた次第」へ');
    }

    public function test_onboarding_card_is_hidden_once_a_meeting_exists(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        Meeting::factory()->for($organization, 'organization')->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('ようこそ、「あなた次第」へ');
    }

    public function test_observer_never_sees_the_onboarding_card(): void
    {
        $organization = Organization::factory()->create();
        $observer = User::factory()->for($organization, 'organization')->observer()->create();

        $response = $this->actingAs($observer)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('ようこそ、「あなた次第」へ');
    }
}
