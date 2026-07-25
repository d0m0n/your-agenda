<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stripe Checkoutからの戻り先(billing.success)まわりのテスト。
 * Webhookの反映が遅れても即座にペイウォールへ戻されないよう、
 * 中間画面(billing.processing)を挟んでbilling.statusをポーリングする設計。
 */
class BillingCheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_page_redirects_to_dashboard_by_default(): void
    {
        $organization = Organization::factory()->create();
        $general = User::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->get(route('billing.success'))
            ->assertOk()
            ->assertSee("window.location.href = '".route('dashboard')."'", false);
    }

    public function test_success_page_redirects_to_the_page_the_user_was_trying_to_reach(): void
    {
        $organization = Organization::factory()->expiredTrial()->create();
        $general = User::factory()->for($organization, 'organization')->create();

        // 未契約のためmaterials.indexにブロックされ、行き先がセッションに残る。
        $this->actingAs($general)->get(route('materials.index'))
            ->assertRedirect(route('billing.paywall'));

        // ナビのロゴ等、画面には常にdashboardのURLも登場するため、
        // ここでは「ポーリング成功時の遷移先」として実際に埋め込まれる
        // window.location.href = '...' の値がmaterials.indexになっている
        // ことをピンポイントで確認する(dashboard固定ではないことの確認)。
        $this->actingAs($general)->get(route('billing.success'))
            ->assertOk()
            ->assertSee("window.location.href = '".route('materials.index')."'", false);
    }

    public function test_status_endpoint_reflects_the_organizations_access_state(): void
    {
        $organization = Organization::factory()->expiredTrial()->create();
        $general = User::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->getJson(route('billing.status'))
            ->assertOk()
            ->assertJson(['active' => false]);

        $organization->forceFill(['free_access_enabled' => true])->save();

        $this->actingAs($general)->getJson(route('billing.status'))
            ->assertOk()
            ->assertJson(['active' => true]);
    }
}
