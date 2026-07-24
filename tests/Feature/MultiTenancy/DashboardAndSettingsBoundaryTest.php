<?php

namespace Tests\Feature\MultiTenancy;

use App\Models\Material;
use App\Models\Meeting;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;
use ZipArchive;

class DashboardAndSettingsBoundaryTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_dashboard_only_shows_own_organizations_data(): void
    {
        [$orgA, $userA] = $this->createTenant();
        [$orgB, $userB] = $this->createTenant();

        Meeting::factory()->for($orgA, 'organization')->create(['name' => '自組織の会議']);
        Meeting::factory()->for($orgB, 'organization')->create(['name' => '他組織の会議']);

        Member::factory()->for($orgA, 'organization')->create(['name' => '自組織の誕生日メンバー', 'birth_date' => now()->subYears(30)]);
        Member::factory()->for($orgB, 'organization')->create(['name' => '他組織の誕生日メンバー', 'birth_date' => now()->subYears(30)]);

        Storage::fake('local');
        Storage::disk('local')->put('materials/a.pdf', 'dummy');
        Storage::disk('local')->put('materials/b.pdf', 'dummy');
        Material::factory()->for($orgA, 'organization')->for($userA)->create(['title' => '自組織の資料', 'file_path' => 'materials/a.pdf']);
        Material::factory()->for($orgB, 'organization')->for($userB)->create(['title' => '他組織の資料', 'file_path' => 'materials/b.pdf']);

        $response = $this->actingAs($userA)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('自組織の会議');
        $response->assertDontSee('他組織の会議');
        $response->assertSee('自組織の誕生日メンバー');
        $response->assertDontSee('他組織の誕生日メンバー');
        $response->assertSee('自組織の資料');
        $response->assertDontSee('他組織の資料');
    }

    public function test_settings_bulk_export_only_contains_own_organizations_meetings(): void
    {
        [$orgA, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $meetingA = Meeting::factory()->for($orgA, 'organization')->create(['name' => '自組織の総会']);
        $meetingB = Meeting::factory()->for($orgB, 'organization')->create(['name' => '他組織の総会']);

        $response = $this->actingAs($userA)->get(route('settings.export'));

        $response->assertOk();

        $zipPath = $response->getFile()->getPathname();
        $zip = new ZipArchive;
        $zip->open($zipPath);

        $entryNames = [];
        $agendaHtml = '';
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            $entryNames[] = $name;
            if (str_ends_with($name, 'agenda.html')) {
                $agendaHtml .= $zip->getFromIndex($i);
            }
        }
        $zip->close();

        $this->assertTrue(collect($entryNames)->contains(
            fn ($name) => str_starts_with($name, $meetingA->id.'_') || str_starts_with($name, $meetingA->id.'/')
        ));
        $this->assertFalse(collect($entryNames)->contains(
            fn ($name) => str_starts_with($name, $meetingB->id.'_') || str_starts_with($name, $meetingB->id.'/')
        ));

        $this->assertStringContainsString('自組織の総会', $agendaHtml);
        $this->assertStringNotContainsString('他組織の総会', $agendaHtml);
    }

    public function test_observer_cannot_access_settings_routes(): void
    {
        [, , $observerA] = $this->createTenant();

        $this->actingAs($observerA)->get(route('settings.edit'))->assertForbidden();
        $this->actingAs($observerA)->get(route('settings.export'))->assertForbidden();
        $this->actingAs($observerA)->put(route('settings.update'), ['name' => '不正更新'])->assertForbidden();
    }

    public function test_meeting_individual_export_contains_only_that_meetings_agenda(): void
    {
        [$orgA, $userA] = $this->createTenant();

        $meetingA = Meeting::factory()->for($orgA, 'organization')->create(['name' => '自組織の総会']);
        $otherMeetingA = Meeting::factory()->for($orgA, 'organization')->create(['name' => '自組織の別会議']);

        $response = $this->actingAs($userA)->get(route('meetings.export', $meetingA));

        $response->assertOk();

        $zipPath = $response->getFile()->getPathname();
        $zip = new ZipArchive;
        $zip->open($zipPath);

        $agendaHtml = '';
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_ends_with($name, 'agenda.html')) {
                $agendaHtml .= $zip->getFromIndex($i);
            }
        }
        $zip->close();

        $this->assertStringContainsString('自組織の総会', $agendaHtml);
        $this->assertStringNotContainsString('自組織の別会議', $agendaHtml);
    }

    public function test_general_user_cannot_export_another_organizations_meeting(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $meetingB = Meeting::factory()->for($orgB, 'organization')->create();

        $this->actingAs($userA)->get(route('meetings.export', $meetingB))->assertNotFound();
    }

    public function test_observer_cannot_export_a_meeting(): void
    {
        [$orgA, , $observerA] = $this->createTenant();
        $meetingA = Meeting::factory()->for($orgA, 'organization')->create();

        $this->actingAs($observerA)->get(route('meetings.export', $meetingA))->assertForbidden();
    }
}
