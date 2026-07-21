<?php

namespace Tests\Feature\MultiTenancy;

use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class PositionBoundaryTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_general_user_cannot_view_edit_form_of_another_organizations_position(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $positionB = Position::factory()->for($orgB, 'organization')->create();

        $this->actingAs($userA)
            ->get(route('positions.edit', $positionB))
            ->assertNotFound();
    }

    public function test_general_user_cannot_update_another_organizations_position(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $positionB = Position::factory()->for($orgB, 'organization')->create(['name' => '他組織の理事長']);

        $this->actingAs($userA)
            ->put(route('positions.update', $positionB), ['name' => '書き換え', 'serial_number' => $positionB->serial_number])
            ->assertNotFound();

        $this->assertSame('他組織の理事長', $positionB->fresh()->name);
    }

    public function test_general_user_cannot_delete_another_organizations_position(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $positionB = Position::factory()->for($orgB, 'organization')->create();

        $this->actingAs($userA)
            ->delete(route('positions.destroy', $positionB))
            ->assertNotFound();

        $this->assertModelExists($positionB);
    }

    public function test_position_index_does_not_list_other_organizations_positions(): void
    {
        [$orgA, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        Position::factory()->for($orgA, 'organization')->create(['name' => '自組織の役職']);
        Position::factory()->for($orgB, 'organization')->create(['name' => '他組織の役職']);

        $response = $this->actingAs($userA)->get(route('positions.index'));

        $response->assertOk();
        $response->assertSee('自組織の役職');
        $response->assertDontSee('他組織の役職');
    }

    public function test_position_serial_number_uniqueness_is_scoped_per_organization(): void
    {
        [$orgA, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        Position::factory()->for($orgB, 'organization')->create(['serial_number' => 1]);

        $response = $this->actingAs($userA)->post(route('positions.store'), [
            'serial_number' => 1,
            'name' => '理事長',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('positions', ['organization_id' => $orgA->id, 'serial_number' => 1, 'name' => '理事長']);
    }

    public function test_observer_cannot_access_position_management_routes(): void
    {
        [$orgA, , $observerA] = $this->createTenant();

        $position = Position::factory()->for($orgA, 'organization')->create();

        $this->actingAs($observerA)->get(route('positions.index'))->assertForbidden();
        $this->actingAs($observerA)->get(route('positions.create'))->assertForbidden();
        $this->actingAs($observerA)->get(route('positions.edit', $position))->assertForbidden();
        $this->actingAs($observerA)->put(route('positions.update', $position), ['name' => '不正更新', 'serial_number' => $position->serial_number])->assertForbidden();
        $this->actingAs($observerA)->delete(route('positions.destroy', $position))->assertForbidden();

        $this->assertModelExists($position);
    }
}
