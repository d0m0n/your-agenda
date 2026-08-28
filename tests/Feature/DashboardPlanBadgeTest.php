<?php

namespace Tests\Feature;

use App\Enums\OrganizationPlan;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ダッシュボードのヘッダーに、現在契約中のプラン(スタンダード/プラス)を
 * ひと目で確認できるバッジを表示する(基本設定画面での確認とは別に、
 * 開いた瞬間に分かるようにする目的)。
 */
class DashboardPlanBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_user_sees_the_standard_plan_badge_linking_to_settings(): void
    {
        $organization = Organization::factory()->create(['plan' => OrganizationPlan::Standard]);
        $general = User::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('スタンダード')
            ->assertSee('href="'.route('settings.edit').'"', false);
    }

    public function test_general_user_sees_the_plus_plan_badge(): void
    {
        $organization = Organization::factory()->create(['plan' => OrganizationPlan::Plus]);
        $general = User::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('プラス')
            ->assertSee('bg-leather-500', false);
    }

    /**
     * オブザーブユーザーはsettings.editへのアクセス権が無いため、バッジは
     * リンクではなく静的なテキストとして表示する。
     */
    public function test_observer_sees_the_plan_badge_without_a_link_to_settings(): void
    {
        $organization = Organization::factory()->create(['plan' => OrganizationPlan::Standard]);
        $observer = User::factory()->for($organization, 'organization')->observer()->create();

        $this->actingAs($observer)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('スタンダード')
            ->assertDontSee('href="'.route('settings.edit').'"', false);
    }
}
