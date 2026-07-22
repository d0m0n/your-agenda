<?php

namespace Tests\Feature;

use App\Models\AgendaItem;
use App\Models\Material;
use App\Models\Meeting;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaterialFileReplaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function uploadInitialMaterial(User $user, string $title = '資料'): Material
    {
        $this->actingAs($user)->post(route('materials.store'), [
            'title' => $title,
            'file' => UploadedFile::fake()->create('original.pdf', 10, 'application/pdf'),
        ]);

        return Material::where('title', $title)->firstOrFail();
    }

    public function test_replacing_a_material_swaps_the_file_but_keeps_the_same_id_title_and_agenda_links(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $material = $this->uploadInitialMaterial($user);
        $originalPath = $material->file_path;

        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $agendaItem = AgendaItem::create([
            'meeting_id' => $meeting->id, 'order' => 1, 'title' => '議題1', 'material_id' => $material->id,
        ]);

        Storage::disk('local')->assertExists($originalPath);

        $response = $this->actingAs($user)->put(route('materials.update', $material), [
            'file' => UploadedFile::fake()->create('replacement.pdf', 5, 'application/pdf'),
        ]);

        $response->assertRedirect(route('materials.index'));
        $response->assertSessionHasNoErrors();

        $material->refresh();
        $this->assertSame('資料', $material->title, 'title is untouched by a plain file replacement');
        $this->assertSame('replacement.pdf', $material->original_filename);
        $this->assertNotSame($originalPath, $material->file_path);

        Storage::disk('local')->assertExists($material->file_path);
        Storage::disk('local')->assertMissing($originalPath, 'old file is cleaned up');

        $this->assertSame($material->id, $agendaItem->fresh()->material_id, 'the agenda item still points at the same material row');
        $this->actingAs($user)->get(route('materials.download', $material))->assertOk();
    }

    public function test_an_invalid_replacement_file_leaves_the_existing_upload_untouched(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();
        $material = $this->uploadInitialMaterial($user);
        $originalPath = $material->file_path;

        $response = $this->actingAs($user)->put(route('materials.update', $material), [
            'file' => UploadedFile::fake()->create('malware.exe', 5, 'application/octet-stream'),
        ]);

        $response->assertSessionHasErrors('file');

        $material->refresh();
        $this->assertSame($originalPath, $material->file_path, 'file_path is unchanged after a failed replacement');
        Storage::disk('local')->assertExists($originalPath);
    }

    public function test_replacement_is_rejected_when_the_new_file_would_push_usage_over_quota(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();

        $small = UploadedFile::fake()->image('small.png', 20, 20);
        $smallBytes = filesize($small->getRealPath());

        $this->actingAs($user)->post(route('materials.store'), ['title' => '資料', 'file' => $small]);
        $material = Material::where('title', '資料')->firstOrFail();

        $large = UploadedFile::fake()->image('large.png', 1200, 1200);
        $largeBytes = filesize($large->getRealPath());
        $this->assertGreaterThan($smallBytes, $largeBytes, 'test fixture assumption: the replacement must be the bigger file');

        $user->update(['storage_quota_bytes' => $smallBytes + 100]);

        $response = $this->actingAs($user)->put(route('materials.update', $material), ['file' => $large]);

        $response->assertSessionHasErrors('file');
        $material->refresh();
        Storage::disk('local')->assertExists($material->file_path);
        $this->assertSame($smallBytes, Storage::disk('local')->size($material->file_path), 'the original file on disk is untouched');
    }

    public function test_replacement_with_a_smaller_file_succeeds_even_when_already_at_the_quota_limit(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->for($organization, 'organization')->create();

        $large = UploadedFile::fake()->image('large.png', 1200, 1200);
        $largeBytes = filesize($large->getRealPath());

        $this->actingAs($user)->post(route('materials.store'), ['title' => '資料', 'file' => $large]);
        $material = Material::where('title', '資料')->firstOrFail();

        $small = UploadedFile::fake()->image('small.png', 20, 20);
        $smallBytes = filesize($small->getRealPath());
        $this->assertLessThan($largeBytes, $smallBytes, 'test fixture assumption: the replacement must be the smaller file');

        $user->update(['storage_quota_bytes' => $largeBytes]);

        $response = $this->actingAs($user)->put(route('materials.update', $material), ['file' => $small]);

        $response->assertRedirect(route('materials.index'));
        $response->assertSessionHasNoErrors();
        $material->refresh();
        $this->assertSame($smallBytes, Storage::disk('local')->size($material->file_path));
    }
}
