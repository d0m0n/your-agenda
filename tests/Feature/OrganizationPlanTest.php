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
 * プラス限定機能を将来追加できるようにするための準備段階のテスト。
 * プラス限定機能自体はまだ存在しないため、ここではorganizations.planの
 * 管理者パネルからの切り替えと、Gate('plus')/hasPlusAccess()の判定ロジックのみを検証する。
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
}
