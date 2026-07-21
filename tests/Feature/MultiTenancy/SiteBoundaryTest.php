<?php

namespace Tests\Feature\MultiTenancy;

use App\Models\Meeting;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class SiteBoundaryTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_general_user_cannot_delete_another_organizations_site(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB, $userB] = $this->createTenant();

        $siteB = Site::factory()->for($orgB, 'organization')->for($userB)->create();

        $this->actingAs($userA)
            ->delete(route('sites.destroy', $siteB))
            ->assertNotFound();

        $this->assertModelExists($siteB);
    }

    public function test_general_user_cannot_upload_a_site_for_another_organizations_meeting(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $meetingB = Meeting::factory()->for($orgB, 'organization')->create();

        $zip = $this->makeMinimalSiteZip();

        $this->actingAs($userA)
            ->post(route('meetings.sites.store', $meetingB), [
                'title' => 'なりすまし議案',
                'zip_file' => $zip,
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('sites', ['title' => 'なりすまし議案']);
    }

    public function test_site_index_does_not_list_other_organizations_sites(): void
    {
        [$orgA, $userA] = $this->createTenant();
        [$orgB, $userB] = $this->createTenant();

        Site::factory()->for($orgA, 'organization')->for($userA)->create(['title' => '自組織の議案']);
        Site::factory()->for($orgB, 'organization')->for($userB)->create(['title' => '他組織の議案']);

        $response = $this->actingAs($userA)->get(route('sites.index'));

        $response->assertOk();
        $response->assertSee('自組織の議案');
        $response->assertDontSee('他組織の議案');
    }

    public function test_observer_cannot_access_site_management_routes(): void
    {
        [$orgA, $userA, $observerA] = $this->createTenant();

        $site = Site::factory()->for($orgA, 'organization')->for($userA)->create();

        $this->actingAs($observerA)->get(route('sites.index'))->assertForbidden();
        $this->actingAs($observerA)->get(route('sites.create'))->assertForbidden();
        $this->actingAs($observerA)->delete(route('sites.destroy', $site))->assertForbidden();

        $this->assertModelExists($site);
    }

    private function makeMinimalSiteZip(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'site').'.zip';
        $zip = new \ZipArchive;
        $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('gian.htm', '<html><body>議案</body></html>');
        $zip->close();

        return new UploadedFile($path, 'gian.zip', 'application/zip', null, true);
    }
}
