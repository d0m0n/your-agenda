<?php

namespace Tests\Feature;

use App\Models\AgendaItem;
use App\Models\Meeting;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class SiteFileReplaceTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    private function uploadInitialSite(object $user, Meeting $meeting, string $title = '議案'): Site
    {
        $this->actingAs($user)->post(route('meetings.sites.store', $meeting), [
            'title' => $title,
            'zip_file' => UploadedFile::fake()->create('resolution.pdf', 10, 'application/pdf'),
        ]);

        return $meeting->sites()->where('title', $title)->firstOrFail();
    }

    public function test_replacing_a_site_swaps_the_file_but_keeps_the_same_uuid_title_and_agenda_links(): void
    {
        [$organization, $user] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $site = $this->uploadInitialSite($user, $meeting);
        $originalUuid = $site->uuid;

        $agendaItem = AgendaItem::create([
            'meeting_id' => $meeting->id,
            'order' => 1,
            'title' => '議題1',
            'site_id' => $site->id,
        ]);

        $this->assertFileExists(storage_path("app/public/sites/{$originalUuid}/document.pdf"));

        $image = UploadedFile::fake()->image('slide.png');

        $response = $this->actingAs($user)->put(route('meetings.sites.update', [$meeting, $site]), [
            'zip_file' => $image,
        ]);

        $response->assertRedirect(route('meetings.edit', $meeting));
        $response->assertSessionHasNoErrors();

        $site->refresh();
        $this->assertSame($originalUuid, $site->uuid, 'uuid (public URL) stays the same after replacing');
        $this->assertSame('議案', $site->title, 'title is untouched by a plain file replacement');
        $this->assertSame('document.png', $site->index_path);

        $this->assertFileExists(storage_path("app/public/sites/{$originalUuid}/document.png"));
        $this->assertFileDoesNotExist(storage_path("app/public/sites/{$originalUuid}/document.pdf"), 'old file is cleaned up');

        $this->assertSame($site->id, $agendaItem->fresh()->site_id, 'the agenda item still points at the same site row');
    }

    public function test_meeting_edit_screen_shows_both_the_original_upload_and_replacement_dates(): void
    {
        [$organization, $user] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $site = $this->uploadInitialSite($user, $meeting);

        // Force a real gap between created_at and updated_at so the "replaced"
        // label is guaranteed to differ regardless of how fast the test runs.
        $site->forceFill(['created_at' => now()->subMinutes(10)])->save();

        $this->actingAs($user)->put(route('meetings.sites.update', [$meeting, $site]), [
            'zip_file' => UploadedFile::fake()->image('slide.png'),
        ]);
        $site->refresh();

        $response = $this->actingAs($user)->get(route('meetings.edit', $meeting));

        $response->assertOk();
        $response->assertSee('アップロード: '.$site->created_at->format('Y-m-d H:i'));
        $response->assertSee('差し替え: '.$site->updated_at->format('Y-m-d H:i'));
    }

    public function test_an_invalid_replacement_file_leaves_the_existing_upload_untouched(): void
    {
        [$organization, $user] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $site = $this->uploadInitialSite($user, $meeting);

        $badZip = UploadedFile::fake()->create('empty.zip', 5, 'application/zip');

        $response = $this->actingAs($user)->put(route('meetings.sites.update', [$meeting, $site]), [
            'zip_file' => $badZip,
        ]);

        $response->assertSessionHasErrors('zip_file');

        $site->refresh();
        $this->assertSame('document.pdf', $site->index_path, 'index_path is unchanged after a failed replacement');
        $this->assertFileExists(storage_path("app/public/sites/{$site->uuid}/document.pdf"), 'original file survives a failed replacement attempt');
    }

    public function test_general_user_cannot_replace_a_site_belonging_to_another_organizations_meeting(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB, $userB] = $this->createTenant();

        $meetingB = Meeting::factory()->for($orgB, 'organization')->create();
        $siteB = $this->uploadInitialSite($userB, $meetingB);

        $response = $this->actingAs($userA)->put(route('meetings.sites.update', [$meetingB, $siteB]), [
            'zip_file' => UploadedFile::fake()->image('new.png'),
        ]);

        $response->assertNotFound();
        $this->assertSame('document.pdf', $siteB->fresh()->index_path);
    }

    public function test_observer_cannot_replace_a_site(): void
    {
        [$orgA, $userA, $observerA] = $this->createTenant();
        $meeting = Meeting::factory()->for($orgA, 'organization')->create();
        $site = $this->uploadInitialSite($userA, $meeting);

        $this->actingAs($observerA)
            ->put(route('meetings.sites.update', [$meeting, $site]), [
                'zip_file' => UploadedFile::fake()->image('new.png'),
            ])
            ->assertForbidden();
    }

    public function test_replacement_is_rejected_when_the_new_file_would_push_usage_over_quota(): void
    {
        // UploadedFile::fake()->create() reports a fake size but writes an
        // empty temp file, which defeats StorageUsageService's real on-disk
        // measurement. fake()->image() writes genuine (small) image bytes,
        // so its real filesize() is what actually lands on disk.
        [$organization, $user] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $small = UploadedFile::fake()->image('small.png', 20, 20);
        $smallBytes = filesize($small->getRealPath());

        $this->actingAs($user)->post(route('meetings.sites.store', $meeting), [
            'title' => '議案',
            'zip_file' => $small,
        ]);
        $site = $meeting->sites()->where('title', '議案')->firstOrFail();

        $large = UploadedFile::fake()->image('large.png', 1200, 1200);
        $largeBytes = filesize($large->getRealPath());
        $this->assertGreaterThan($smallBytes, $largeBytes, 'test fixture assumption: the replacement must be the bigger file');

        // Just enough headroom for the current (small) file, not for the large one.
        $user->update(['storage_quota_bytes' => $smallBytes + 100]);

        $response = $this->actingAs($user)->put(route('meetings.sites.update', [$meeting, $site]), [
            'zip_file' => $large,
        ]);

        $response->assertSessionHasErrors('zip_file');
        $this->assertSame('document.png', $site->fresh()->index_path);
        $this->assertFileExists(storage_path("app/public/sites/{$site->uuid}/document.png"));
        $this->assertSame($smallBytes, filesize(storage_path("app/public/sites/{$site->uuid}/document.png")), 'the original file on disk is untouched');
    }

    public function test_replacement_with_a_smaller_file_succeeds_even_when_already_at_the_quota_limit(): void
    {
        [$organization, $user] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $large = UploadedFile::fake()->image('large.png', 1200, 1200);
        $largeBytes = filesize($large->getRealPath());

        $this->actingAs($user)->post(route('meetings.sites.store', $meeting), [
            'title' => '議案',
            'zip_file' => $large,
        ]);
        $site = $meeting->sites()->where('title', '議案')->firstOrFail();

        $small = UploadedFile::fake()->image('small.png', 20, 20);
        $smallBytes = filesize($small->getRealPath());
        $this->assertLessThan($largeBytes, $smallBytes, 'test fixture assumption: the replacement must be the smaller file');

        // No headroom at all beyond what's already used by the large file.
        $user->update(['storage_quota_bytes' => $largeBytes]);

        $response = $this->actingAs($user)->put(route('meetings.sites.update', [$meeting, $site]), [
            'zip_file' => $small,
        ]);

        $response->assertRedirect(route('meetings.edit', $meeting));
        $response->assertSessionHasNoErrors();
        $this->assertSame('document.png', $site->fresh()->index_path);
        $this->assertSame($smallBytes, filesize(storage_path("app/public/sites/{$site->uuid}/document.png")));
    }
}
