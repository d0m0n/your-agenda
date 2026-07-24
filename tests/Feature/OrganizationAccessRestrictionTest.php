<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AgendaItem;
use App\Models\Material;
use App\Models\Meeting;
use App\Models\Organization;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * 解約・トライアル終了後も次第(会議)の閲覧だけは可能にしつつ、リンクされた
 * 議案データ(sites/materials)は開けなくする挙動、外部公開共有リンクの
 * ゲート化、管理者による無償提供モードのテスト。
 */
class OrganizationAccessRestrictionTest extends TestCase
{
    use RefreshDatabase;

    private function makeSuperAdmin(): User
    {
        return User::factory()->create([
            'organization_id' => null,
            'role' => UserRole::SuperAdmin,
        ]);
    }

    public function test_meetings_index_and_show_remain_viewable_after_trial_expires(): void
    {
        $organization = Organization::factory()->expiredTrial()->create();
        $general = User::factory()->for($organization, 'organization')->create();
        $observer = User::factory()->for($organization, 'organization')->observer()->create();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->get(route('meetings.index'))->assertOk();
        $this->actingAs($general)->get(route('meetings.show', $meeting))->assertOk();
        $this->actingAs($observer)->get(route('meetings.index'))->assertOk();
        $this->actingAs($observer)->get(route('meetings.show', $meeting))->assertOk();
    }

    public function test_meeting_creation_stays_blocked_after_trial_expires(): void
    {
        $organization = Organization::factory()->expiredTrial()->create();
        $general = User::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->get(route('meetings.create'))->assertRedirect(route('billing.paywall'));
    }

    public function test_site_link_is_blocked_after_trial_expires_but_works_when_active(): void
    {
        $organization = Organization::factory()->expiredTrial()->create();
        $general = User::factory()->for($organization, 'organization')->create();
        $site = Site::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->get(route('sites.open', $site))->assertRedirect(route('billing.paywall'));

        // trial_ends_atはユーザー入力からのFillableに含めていないため、
        // 登録フロー同様forceFillで更新する。$generalは1つ目のリクエストで
        // organizationリレーションをキャッシュ済みのため、明示的にrefreshする。
        $organization->forceFill(['trial_ends_at' => now()->addDays(14)])->save();
        $general->refresh();

        $this->actingAs($general)->get(route('sites.open', $site))->assertRedirect($site->publicUrl());
    }

    public function test_agenda_item_link_url_points_to_the_gated_route(): void
    {
        $organization = Organization::factory()->create();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $site = Site::factory()->for($organization, 'organization')->create(['meeting_id' => $meeting->id]);
        $item = AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '議案A', 'site_id' => $site->id]);

        $this->assertSame(route('sites.open', $site), $item->linkUrl());
    }

    public function test_free_access_enabled_grants_access_regardless_of_trial_state(): void
    {
        $organization = Organization::factory()->expiredTrial()->create(['free_access_enabled' => true]);

        $this->assertTrue($organization->hasActiveAccess());
        $this->assertSame('無償提供中', $organization->subscriptionStatusLabel());
    }

    public function test_super_admin_can_toggle_free_access_for_an_organization(): void
    {
        $admin = $this->makeSuperAdmin();
        $organization = Organization::factory()->create(['free_access_enabled' => false]);

        $this->actingAs($admin)
            ->patch(route('admin.organizations.toggle-free-access', $organization))
            ->assertRedirect(route('admin.organizations.show', $organization));

        $this->assertTrue($organization->fresh()->free_access_enabled);

        $this->actingAs($admin)->patch(route('admin.organizations.toggle-free-access', $organization));

        $this->assertFalse($organization->fresh()->free_access_enabled);
    }

    public function test_general_and_observer_cannot_toggle_free_access(): void
    {
        $organization = Organization::factory()->create();
        $general = User::factory()->for($organization, 'organization')->create();
        $observer = User::factory()->for($organization, 'organization')->observer()->create();

        $this->actingAs($general)->patch(route('admin.organizations.toggle-free-access', $organization))->assertForbidden();
        $this->actingAs($observer)->patch(route('admin.organizations.toggle-free-access', $organization))->assertForbidden();
    }

    public function test_public_share_link_shows_unavailable_notice_when_organization_has_expired(): void
    {
        $organization = Organization::factory()->expiredTrial()->create();
        $meeting = Meeting::factory()->for($organization, 'organization')->create([
            'public_token' => (string) Str::uuid(),
        ]);

        $response = $this->get(route('public.meetings.show', $meeting->public_token));

        $response->assertOk();
        $response->assertSee(__('このページは現在ご利用いただけません'));
        $response->assertDontSee($meeting->name);
    }

    public function test_public_share_link_works_normally_when_organization_has_active_access(): void
    {
        $organization = Organization::factory()->create();
        $meeting = Meeting::factory()->for($organization, 'organization')->create([
            'public_token' => (string) Str::uuid(),
        ]);

        $response = $this->get(route('public.meetings.show', $meeting->public_token));

        $response->assertOk();
        $response->assertSee($meeting->name);
    }

    public function test_public_material_and_site_downloads_are_blocked_when_organization_has_expired(): void
    {
        $organization = Organization::factory()->expiredTrial()->create();
        $meeting = Meeting::factory()->for($organization, 'organization')->create([
            'public_token' => (string) Str::uuid(),
        ]);
        $site = Site::factory()->for($organization, 'organization')->create(['meeting_id' => $meeting->id]);
        $material = Material::factory()->for($organization, 'organization')->create();
        AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '議案A', 'site_id' => $site->id]);
        AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 2, 'title' => '議案B', 'material_id' => $material->id]);

        $this->get(route('public.meetings.sites.open', ['meeting' => $meeting->public_token, 'site' => $site]))
            ->assertNotFound();
        $this->get(route('public.meetings.materials.download', ['meeting' => $meeting->public_token, 'material' => $material]))
            ->assertNotFound();
    }

    public function test_access_blocked_banner_shown_when_blocked_and_hidden_on_paywall_page(): void
    {
        $organization = Organization::factory()->expiredTrial()->create();
        $general = User::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->get(route('meetings.index'))
            ->assertOk()
            ->assertSee(__('今すぐお支払い情報を登録する'));

        $response = $this->actingAs($general)->get(route('billing.paywall'));
        $response->assertOk();
        // ペイウォール画面自体には常設バナーを重ねて出さない。
        $response->assertSee(__('お支払い情報を登録して利用を再開する'));
        $response->assertDontSee(__('今すぐお支払い情報を登録する'));
    }

    public function test_access_blocked_banner_is_not_shown_for_active_organization(): void
    {
        $organization = Organization::factory()->create();
        $general = User::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->get(route('meetings.index'))
            ->assertOk()
            ->assertDontSee(__('今すぐお支払い情報を登録する'));
    }
}
