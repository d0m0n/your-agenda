<?php

namespace Tests\Feature\MultiTenancy;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class ObserverUserBoundaryTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_general_user_cannot_view_edit_form_of_another_organizations_observer(): void
    {
        [, $userA] = $this->createTenant();
        [, , $observerB] = $this->createTenant();

        $this->actingAs($userA)
            ->get(route('observers.edit', $observerB))
            ->assertNotFound();
    }

    public function test_general_user_cannot_update_another_organizations_observer(): void
    {
        [, $userA] = $this->createTenant();
        [, , $observerB] = $this->createTenant();
        $originalName = $observerB->name;

        $this->actingAs($userA)
            ->put(route('observers.update', $observerB), [
                'name' => '書き換え',
                'email' => $observerB->email,
            ])
            ->assertNotFound();

        $this->assertSame($originalName, $observerB->fresh()->name);
    }

    public function test_general_user_cannot_delete_another_organizations_observer(): void
    {
        [, $userA] = $this->createTenant();
        [, , $observerB] = $this->createTenant();

        $this->actingAs($userA)
            ->delete(route('observers.destroy', $observerB))
            ->assertNotFound();

        $this->assertModelExists($observerB);
    }

    public function test_general_user_cannot_manage_a_general_user_through_observer_routes(): void
    {
        [$orgA, $userA] = $this->createTenant();

        $otherGeneralUser = User::factory()->for($orgA, 'organization')->create(['role' => UserRole::General]);

        $this->actingAs($userA)
            ->get(route('observers.edit', $otherGeneralUser))
            ->assertNotFound();

        $this->actingAs($userA)
            ->delete(route('observers.destroy', $otherGeneralUser))
            ->assertNotFound();

        $this->assertModelExists($otherGeneralUser);
    }

    public function test_observer_index_does_not_list_other_organizations_observers(): void
    {
        [$orgA, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $ownObserver = User::factory()->for($orgA, 'organization')->observer()->create(['name' => '自組織オブザーバー']);
        User::factory()->for($orgB, 'organization')->observer()->create(['name' => '他組織オブザーバー']);

        $response = $this->actingAs($userA)->get(route('observers.index'));

        $response->assertOk();
        $response->assertSee('自組織オブザーバー');
        $response->assertDontSee('他組織オブザーバー');
        $this->assertModelExists($ownObserver);
    }

    public function test_observer_cannot_access_observer_management_routes(): void
    {
        [$orgA, , $observerA] = $this->createTenant();

        $anotherObserver = User::factory()->for($orgA, 'organization')->observer()->create();

        $this->actingAs($observerA)->get(route('observers.index'))->assertForbidden();
        $this->actingAs($observerA)->get(route('observers.create'))->assertForbidden();
        $this->actingAs($observerA)->get(route('observers.edit', $anotherObserver))->assertForbidden();
        $this->actingAs($observerA)->delete(route('observers.destroy', $anotherObserver))->assertForbidden();

        $this->assertModelExists($anotherObserver);
    }
}
