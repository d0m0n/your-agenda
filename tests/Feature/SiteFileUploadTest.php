<?php

namespace Tests\Feature;

use App\Models\Meeting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class SiteFileUploadTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_pdf_file_can_be_uploaded_and_opened_without_a_gian_named_file(): void
    {
        [$organization, $user] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $pdf = UploadedFile::fake()->create('resolution.pdf', 10, 'application/pdf');

        $this->actingAs($user)
            ->post(route('meetings.sites.store', $meeting), [
                'title' => 'PDF議案',
                'zip_file' => $pdf,
            ])
            ->assertRedirect(route('meetings.edit', $meeting));

        $this->assertDatabaseHas('sites', [
            'title' => 'PDF議案',
            'index_path' => 'document.pdf',
        ]);

        $site = $organization->sites()->where('title', 'PDF議案')->firstOrFail();
        $this->assertFileExists(storage_path("app/public/sites/{$site->uuid}/document.pdf"));
    }

    public function test_image_file_can_be_uploaded_and_opened_directly(): void
    {
        [$organization, $user] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $image = UploadedFile::fake()->image('slide.png');

        $this->actingAs($user)
            ->post(route('meetings.sites.store', $meeting), [
                'title' => '画像議案',
                'zip_file' => $image,
            ])
            ->assertRedirect(route('meetings.edit', $meeting));

        $this->assertDatabaseHas('sites', [
            'title' => '画像議案',
            'index_path' => 'document.png',
        ]);
    }

    public function test_meeting_edit_screen_shows_the_upload_date_for_each_site(): void
    {
        [$organization, $user] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $this->actingAs($user)->post(route('meetings.sites.store', $meeting), [
            'title' => 'PDF議案',
            'zip_file' => UploadedFile::fake()->create('resolution.pdf', 10, 'application/pdf'),
        ]);
        $site = $meeting->sites()->where('title', 'PDF議案')->firstOrFail();

        $response = $this->actingAs($user)->get(route('meetings.edit', $meeting));

        $response->assertOk();
        $response->assertSee('アップロード: '.$site->created_at->format('Y-m-d H:i'));
        $response->assertDontSee('差し替え:', false);
    }

    public function test_unsupported_file_type_is_rejected(): void
    {
        [$organization, $user] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $exe = UploadedFile::fake()->create('malware.exe', 10, 'application/octet-stream');

        $this->actingAs($user)
            ->post(route('meetings.sites.store', $meeting), [
                'title' => '不正ファイル',
                'zip_file' => $exe,
            ])
            ->assertSessionHasErrors('zip_file');

        $this->assertDatabaseMissing('sites', ['title' => '不正ファイル']);
    }
}
