<?php

namespace Tests\Feature;

use App\Enums\OrganizationPlan;
use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

/**
 * プラン(スタンダード/プラス)の切り替えと、Gate('plus')/hasPlusAccess()の
 * 判定ロジックのテスト。組織の基本設定画面からのセルフサービス切替
 * (BillingController::updatePlan())は、既にStripeサブスクリプションが
 * ある組織に対してはSubscription::swap()で実際にStripe APIを呼ぶため、
 * BillingTest.phpの方針(実際の決済経路は自動テスト対象外)と同じ理由で
 * その分岐は自動テストしない。ここでテストするのは、Stripeへの通信が
 * 発生しない分岐(トライアル中でまだ契約していない組織の切替、同一プランへの
 * 変更、権限チェック)のみ。
 */
class OrganizationPlanTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    private function makeSuperAdmin(): User
    {
        return User::factory()->create([
            'organization_id' => null,
            'role' => UserRole::SuperAdmin,
        ]);
    }

    public function test_organizations_default_to_the_standard_plan(): void
    {
        // 'plan'はDB側のカラムdefaultで補完されるため、create()直後の
        // インメモリなモデルではなくfresh()で読み直した値を検証する。
        // また、ファクトリはデフォルトでトライアル中(遠い未来のtrial_ends_at)の
        // ため、hasPlusAccess()の確認はexpiredTrial()で別途行う
        // (test_expired_trial_standard_plan_does_not_have_plus_access)。
        $organization = Organization::factory()->create()->fresh();

        $this->assertSame(OrganizationPlan::Standard, $organization->plan);
    }

    public function test_plus_plan_grants_plus_access(): void
    {
        // トライアル中はplanに関わらずtrueになるため、expiredTrial()で
        // プラン自体の効果だけを切り分けて検証する。
        $organization = Organization::factory()->expiredTrial()->create(['plan' => OrganizationPlan::Plus]);

        $this->assertTrue($organization->hasPlusAccess());
    }

    public function test_trial_and_free_access_grant_plus_access_regardless_of_plan(): void
    {
        $trialOrganization = Organization::factory()->create(['plan' => OrganizationPlan::Standard]);
        $this->assertTrue($trialOrganization->hasPlusAccess());

        $freeOrganization = Organization::factory()->expiredTrial()->create([
            'plan' => OrganizationPlan::Standard,
            'free_access_enabled' => true,
        ]);
        $this->assertTrue($freeOrganization->hasPlusAccess());
    }

    public function test_expired_trial_standard_plan_does_not_have_plus_access(): void
    {
        $organization = Organization::factory()->expiredTrial()->create(['plan' => OrganizationPlan::Standard]);

        $this->assertFalse($organization->hasPlusAccess());
    }

    public function test_plus_gate_follows_the_organizations_plan(): void
    {
        [$organization, $general] = $this->createTenant();
        $organization->forceFill(['plan' => OrganizationPlan::Plus])->save();
        $general->refresh();

        $this->assertTrue($general->can('plus'));
    }

    public function test_super_admin_can_change_an_organizations_plan(): void
    {
        $admin = $this->makeSuperAdmin();
        $organization = Organization::factory()->create(['plan' => OrganizationPlan::Standard]);

        $this->actingAs($admin)
            ->patch(route('admin.organizations.update-plan', $organization), ['plan' => 'plus'])
            ->assertRedirect(route('admin.organizations.show', $organization));

        $this->assertSame(OrganizationPlan::Plus, $organization->fresh()->plan);
    }

    public function test_invalid_plan_value_is_rejected(): void
    {
        $admin = $this->makeSuperAdmin();
        $organization = Organization::factory()->create(['plan' => OrganizationPlan::Standard]);

        $this->actingAs($admin)
            ->patch(route('admin.organizations.update-plan', $organization), ['plan' => 'premium'])
            ->assertSessionHasErrors('plan');

        $this->assertSame(OrganizationPlan::Standard, $organization->fresh()->plan);
    }

    public function test_general_and_observer_cannot_change_the_plan(): void
    {
        [$organization, $general, $observer] = $this->createTenant();

        $this->actingAs($general)->patch(route('admin.organizations.update-plan', $organization), ['plan' => 'plus'])->assertForbidden();
        $this->actingAs($observer)->patch(route('admin.organizations.update-plan', $organization), ['plan' => 'plus'])->assertForbidden();

        $this->assertSame(OrganizationPlan::Standard, $organization->fresh()->plan);
    }

    /**
     * トライアル中(まだStripeサブスクリプションが無い)組織は、Stripeへの
     * 通信なしにローカルのplanだけを切り替えられる。この選択は後日
     * checkout()を呼んだ際にorganization.plan->priceId()として使われる。
     */
    public function test_general_user_can_self_service_switch_plan_during_trial_without_calling_stripe(): void
    {
        [$organization, $general] = $this->createTenant();
        $this->assertFalse($organization->subscribed('default'));

        $this->actingAs($general)->put(route('billing.plan.update'), ['plan' => 'plus'])
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHas('status');

        $this->assertSame(OrganizationPlan::Plus, $organization->fresh()->plan);
    }

    public function test_switching_to_the_currently_selected_plan_is_a_no_op(): void
    {
        [$organization, $general] = $this->createTenant();
        $organization->forceFill(['plan' => OrganizationPlan::Plus])->save();

        $this->actingAs($general)->put(route('billing.plan.update'), ['plan' => 'plus'])
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHas('status');

        $this->assertSame(OrganizationPlan::Plus, $organization->fresh()->plan);
    }

    public function test_observer_cannot_self_service_switch_plan(): void
    {
        [$organization, , $observer] = $this->createTenant();

        $this->actingAs($observer)->put(route('billing.plan.update'), ['plan' => 'plus'])->assertForbidden();

        $this->assertSame(OrganizationPlan::Standard, $organization->fresh()->plan);
    }

    public function test_self_service_plan_switch_rejects_an_invalid_plan_value(): void
    {
        [$organization, $general] = $this->createTenant();

        $this->actingAs($general)->put(route('billing.plan.update'), ['plan' => 'premium'])
            ->assertSessionHasErrors('plan');

        $this->assertSame(OrganizationPlan::Standard, $organization->fresh()->plan);
    }
}
