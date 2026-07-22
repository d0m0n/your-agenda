<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class ProfileAccessTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_observer_cannot_view_profile_page(): void
    {
        [, , $observer] = $this->createTenant();

        $this->actingAs($observer)->get(route('profile.edit'))->assertForbidden();
    }

    public function test_observer_cannot_update_profile(): void
    {
        [, , $observer] = $this->createTenant();

        $this->actingAs($observer)
            ->patch(route('profile.update'), ['name' => '書き換え', 'email' => $observer->email])
            ->assertForbidden();

        $this->assertNotSame('書き換え', $observer->fresh()->name);
    }

    public function test_observer_cannot_delete_own_account(): void
    {
        [, , $observer] = $this->createTenant();

        $this->actingAs($observer)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertForbidden();

        $this->assertModelExists($observer);
    }

    public function test_general_user_can_still_view_profile_page(): void
    {
        [, $general] = $this->createTenant();

        $this->actingAs($general)->get(route('profile.edit'))->assertOk();
    }

    public function test_observer_does_not_see_profile_link_in_navigation(): void
    {
        [, , $observer] = $this->createTenant();

        $response = $this->actingAs($observer)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee(route('profile.edit'), false);
    }

    public function test_general_user_sees_profile_link_in_navigation(): void
    {
        [, $general] = $this->createTenant();

        $response = $this->actingAs($general)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(route('profile.edit'), false);
    }
}
