<?php

namespace Tests\Feature\MultiTenancy;

use App\Models\Material;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class MaterialBoundaryTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_general_user_cannot_download_another_organizations_material(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB, $userB] = $this->createTenant();

        $materialB = $this->createMaterialWithFile($orgB, $userB);

        $this->actingAs($userA)
            ->get(route('materials.download', $materialB))
            ->assertNotFound();
    }

    public function test_general_user_cannot_delete_another_organizations_material(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB, $userB] = $this->createTenant();

        $materialB = $this->createMaterialWithFile($orgB, $userB);

        $this->actingAs($userA)
            ->delete(route('materials.destroy', $materialB))
            ->assertNotFound();

        $this->assertModelExists($materialB);
        Storage::disk('local')->assertExists($materialB->file_path);
    }

    public function test_material_index_does_not_list_other_organizations_materials(): void
    {
        [$orgA, $userA] = $this->createTenant();
        [$orgB, $userB] = $this->createTenant();

        $this->createMaterialWithFile($orgA, $userA, '自組織の資料');
        $this->createMaterialWithFile($orgB, $userB, '他組織の資料');

        $response = $this->actingAs($userA)->get(route('materials.index'));

        $response->assertOk();
        $response->assertSee('自組織の資料');
        $response->assertDontSee('他組織の資料');
    }

    public function test_observer_can_download_own_organizations_material(): void
    {
        [$orgA, $userA, $observerA] = $this->createTenant();

        $material = $this->createMaterialWithFile($orgA, $userA);

        $this->actingAs($observerA)
            ->get(route('materials.download', $material))
            ->assertOk();
    }

    public function test_observer_cannot_download_another_organizations_material(): void
    {
        [, , $observerA] = $this->createTenant();
        [$orgB, $userB] = $this->createTenant();

        $materialB = $this->createMaterialWithFile($orgB, $userB);

        $this->actingAs($observerA)
            ->get(route('materials.download', $materialB))
            ->assertNotFound();
    }

    public function test_observer_cannot_access_material_management_routes(): void
    {
        [$orgA, $userA, $observerA] = $this->createTenant();

        $material = $this->createMaterialWithFile($orgA, $userA);

        $this->actingAs($observerA)->delete(route('materials.destroy', $material))->assertForbidden();
        $this->actingAs($observerA)->put(route('materials.update', $material), [
            'file' => UploadedFile::fake()->create('new.pdf', 10, 'application/pdf'),
        ])->assertForbidden();

        $this->assertModelExists($material);
    }

    public function test_general_user_cannot_replace_another_organizations_material(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB, $userB] = $this->createTenant();

        $materialB = $this->createMaterialWithFile($orgB, $userB);

        $this->actingAs($userA)
            ->put(route('materials.update', $materialB), [
                'file' => UploadedFile::fake()->create('new.pdf', 10, 'application/pdf'),
            ])
            ->assertNotFound();

        Storage::disk('local')->assertExists($materialB->file_path);
    }

    private function createMaterialWithFile(Organization $organization, User $user, string $title = '資料'): Material
    {
        $path = 'materials/'.Str::uuid().'.pdf';
        Storage::disk('local')->put($path, 'dummy-content');

        return Material::factory()->for($organization, 'organization')->for($user)->create([
            'title' => $title,
            'file_path' => $path,
        ]);
    }
}
