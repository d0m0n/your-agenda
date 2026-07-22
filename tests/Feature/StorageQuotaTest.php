<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StorageQuotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_material_upload_succeeds_when_within_quota(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();

        $file = UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf');

        $this->actingAs($user)->post(route('materials.store'), [
            'title' => '資料A',
            'file' => $file,
        ])->assertRedirect(route('materials.index'));

        $this->assertDatabaseHas('materials', ['title' => '資料A']);
    }

    public function test_material_upload_is_rejected_once_the_users_quota_is_reached(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create([
            'storage_quota_bytes' => 1024, // 1KB — trivially exceeded by any real upload
        ]);

        $file = UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf'); // 50KB

        $response = $this->actingAs($user)->post(route('materials.store'), [
            'title' => '資料B',
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('materials', ['title' => '資料B']);
    }

    public function test_settings_page_shows_current_usage_and_quota(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();

        $response = $this->actingAs($user)->get(route('settings.edit'));

        $response->assertOk();
        $response->assertSee('データ使用量');
        $response->assertSee('2.00 GB'); // default quota formatted
    }
}
