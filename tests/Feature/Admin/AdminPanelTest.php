<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Material;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function makeSuperAdmin(): User
    {
        return User::factory()->create([
            'organization_id' => null,
            'role' => UserRole::SuperAdmin,
        ]);
    }

    public function test_general_and_observer_users_cannot_access_the_admin_panel(): void
    {
        $organization = Organization::factory()->create();
        $general = User::factory()->for($organization, 'organization')->create();
        $observer = User::factory()->for($organization, 'organization')->observer()->create();

        $this->actingAs($general)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($observer)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_super_admin_can_view_the_organization_list_and_detail(): void
    {
        $admin = $this->makeSuperAdmin();
        $organization = Organization::factory()->create(['name' => 'テストJC']);
        User::factory()->for($organization, 'organization')->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('テストJC');

        $this->actingAs($admin)->get(route('admin.organizations.show', $organization))
            ->assertOk()
            ->assertSee('テストJC');
    }

    public function test_super_admin_can_change_a_general_users_storage_quota(): void
    {
        $admin = $this->makeSuperAdmin();
        $organization = Organization::factory()->create();
        $general = User::factory()->for($organization, 'organization')->create();

        $this->actingAs($admin)->put(
            route('admin.organizations.users.update-quota', [$organization, $general]),
            ['storage_quota_gb' => 5]
        )->assertRedirect(route('admin.organizations.show', $organization));

        $this->assertSame(5 * 1024 * 1024 * 1024, $general->fresh()->storage_quota_bytes);
    }

    public function test_super_admin_cannot_change_quota_for_a_user_in_a_different_organization(): void
    {
        $admin = $this->makeSuperAdmin();
        $organizationA = Organization::factory()->create();
        $organizationB = Organization::factory()->create();
        $generalB = User::factory()->for($organizationB, 'organization')->create();

        $this->actingAs($admin)->put(
            route('admin.organizations.users.update-quota', [$organizationA, $generalB]),
            ['storage_quota_gb' => 5]
        )->assertNotFound();
    }

    public function test_super_admin_can_delete_a_users_account(): void
    {
        $admin = $this->makeSuperAdmin();
        $organization = Organization::factory()->create();
        $observer = User::factory()->for($organization, 'organization')->observer()->create();

        $this->actingAs($admin)
            ->delete(route('admin.organizations.users.destroy', [$organization, $observer]))
            ->assertRedirect(route('admin.organizations.show', $organization));

        $this->assertModelMissing($observer);
    }

    public function test_super_admin_can_purge_an_organizations_uploaded_files_without_deleting_records(): void
    {
        Storage::fake('local');

        $admin = $this->makeSuperAdmin();
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $material = Material::factory()->for($organization, 'organization')->for($user)->create([
            'file_path' => 'materials/test.txt',
        ]);
        Storage::disk('local')->put($material->file_path, 'dummy content');

        $this->actingAs($admin)
            ->delete(route('admin.organizations.destroy-data', $organization))
            ->assertRedirect(route('admin.organizations.show', $organization));

        $this->assertModelMissing($material);
        Storage::disk('local')->assertMissing('materials/test.txt');
        $this->assertModelExists($organization);
        $this->assertModelExists($user);
    }
}
