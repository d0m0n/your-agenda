<?php

namespace Tests\Feature\MultiTenancy;

use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class DepartmentBoundaryTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_general_user_cannot_view_edit_form_of_another_organizations_department(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $departmentB = Department::factory()->for($orgB, 'organization')->create();

        $this->actingAs($userA)
            ->get(route('departments.edit', $departmentB))
            ->assertNotFound();
    }

    public function test_general_user_cannot_update_another_organizations_department(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $departmentB = Department::factory()->for($orgB, 'organization')->create(['name' => '他組織の委員会']);

        $this->actingAs($userA)
            ->put(route('departments.update', $departmentB), ['name' => '書き換え', 'serial_number' => $departmentB->serial_number])
            ->assertNotFound();

        $this->assertSame('他組織の委員会', $departmentB->fresh()->name);
    }

    public function test_general_user_cannot_delete_another_organizations_department(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $departmentB = Department::factory()->for($orgB, 'organization')->create();

        $this->actingAs($userA)
            ->delete(route('departments.destroy', $departmentB))
            ->assertNotFound();

        $this->assertModelExists($departmentB);
    }

    public function test_department_index_does_not_list_other_organizations_departments(): void
    {
        [$orgA, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        Department::factory()->for($orgA, 'organization')->create(['name' => '自組織の委員会']);
        Department::factory()->for($orgB, 'organization')->create(['name' => '他組織の委員会']);

        $response = $this->actingAs($userA)->get(route('departments.index'));

        $response->assertOk();
        $response->assertSee('自組織の委員会');
        $response->assertDontSee('他組織の委員会');
    }

    public function test_department_serial_number_uniqueness_is_scoped_per_organization(): void
    {
        [$orgA, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        Department::factory()->for($orgB, 'organization')->create(['serial_number' => 1]);

        $response = $this->actingAs($userA)->post(route('departments.store'), [
            'serial_number' => 1,
            'name' => '総務委員会',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('departments', ['organization_id' => $orgA->id, 'serial_number' => 1, 'name' => '総務委員会']);
    }

    public function test_observer_cannot_access_department_management_routes(): void
    {
        [$orgA, , $observerA] = $this->createTenant();

        $department = Department::factory()->for($orgA, 'organization')->create();

        $this->actingAs($observerA)->get(route('departments.index'))->assertForbidden();
        $this->actingAs($observerA)->get(route('departments.create'))->assertForbidden();
        $this->actingAs($observerA)->get(route('departments.edit', $department))->assertForbidden();
        $this->actingAs($observerA)->put(route('departments.update', $department), ['name' => '不正更新', 'serial_number' => $department->serial_number])->assertForbidden();
        $this->actingAs($observerA)->delete(route('departments.destroy', $department))->assertForbidden();

        $this->assertModelExists($department);
    }

    public function test_member_cannot_be_assigned_a_department_belonging_to_another_organization(): void
    {
        [$orgA, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $member = \App\Models\Member::factory()->for($orgA, 'organization')->create();
        $departmentB = Department::factory()->for($orgB, 'organization')->create();

        $response = $this->actingAs($userA)
            ->put(route('members.update', $member), [
                'name' => $member->name,
                'department_id' => $departmentB->id,
            ]);

        $response->assertSessionHasErrors('department_id');
        $this->assertNull($member->fresh()->department_id);
    }
}
