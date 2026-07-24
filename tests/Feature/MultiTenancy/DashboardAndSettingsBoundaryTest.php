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

        $meetingA = Meeting::factory()->for($orgA, 'organization')->create(['name' => '自組織の総会', 'held_at' => '2026-08-22 19:00:00']);
        $meetingB = Meeting::factory()->for($orgB, 'organization')->create(['name' => '他組織の総会', 'held_at' => '2026-09-01 10:00:00']);

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

        // フォルダ名は開催日時のYYYYMMDDhhmm形式(わかりやすさのため、
        // 会議IDやスラッグ化した会議名ではなくこちらを使う)。
        $this->assertTrue(collect($entryNames)->contains(
            fn ($name) => str_starts_with($name, '202608221900/')
        ));
        $this->assertFalse(collect($entryNames)->contains(
            fn ($name) => str_starts_with($name, '202609011000/')
        ));

        $this->assertStringContainsString('自組織の総会', $agendaHtml);
        $this->assertStringNotContainsString('他組織の総会', $agendaHtml);
    }

    public function test_bulk_export_disambiguates_meetings_held_at_the_exact_same_minute(): void
    {
        [$orgA, $userA] = $this->createTenant();

        Meeting::factory()->for($orgA, 'organization')->create(['name' => '第1委員会', 'held_at' => '2026-08-22 19:00:00']);
        Meeting::factory()->for($orgA, 'organization')->create(['name' => '第2委員会', 'held_at' => '2026-08-22 19:00:00']);

        $response = $this->actingAs($userA)->get(route('settings.export'));

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

        // 同じ開催日時(分単位)の会議が2件あっても、片方が上書きされず
        // 両方とも別フォルダにagenda.htmlが残ること(フォルダ名は
        // どちらも"202608221900"で始まるが、片方は会議IDが付与される)。
        $agendaEntries = collect($entryNames)->filter(fn ($name) => str_ends_with($name, 'agenda.html'));
        $this->assertCount(2, $agendaEntries);
        $this->assertTrue($agendaEntries->every(fn ($name) => str_starts_with($name, '202608221900')));
        $this->assertNotSame($agendaEntries->first(), $agendaEntries->last());

        $this->assertStringContainsString('第1委員会', $agendaHtml);
        $this->assertStringContainsString('第2委員会', $agendaHtml);
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
