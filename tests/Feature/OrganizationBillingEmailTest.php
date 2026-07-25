<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

/**
 * 組織の請求先メールアドレス(billing_email)。Cashierの stripeEmail() から
 * 参照され、Stripeの顧客情報(領収書・請求書メールの送付先)に同期される。
 * Stripe APIへの実際の同期(stripe_idがある場合のsyncStripeCustomerDetails呼び出し)は
 * 実APIを呼ぶため自動テスト化していない(BillingTest.php等と同じ方針)。
 */
class OrganizationBillingEmailTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_general_user_can_set_a_billing_email(): void
    {
        [$organization, $general] = $this->createTenant();

        $this->actingAs($general)->put(route('settings.update'), [
            'name' => $organization->name,
            'billing_email' => 'billing@example.com',
        ])->assertRedirect(route('settings.edit'));

        $this->assertSame('billing@example.com', $organization->fresh()->billing_email);
    }

    public function test_billing_email_must_be_a_valid_email_format(): void
    {
        [$organization, $general] = $this->createTenant();

        $response = $this->actingAs($general)->put(route('settings.update'), [
            'name' => $organization->name,
            'billing_email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors('billing_email');
        $this->assertNull($organization->fresh()->billing_email);
    }

    public function test_billing_email_is_optional(): void
    {
        [$organization, $general] = $this->createTenant();

        $this->actingAs($general)->put(route('settings.update'), [
            'name' => $organization->name,
        ])->assertRedirect(route('settings.edit'));

        $this->assertNull($organization->fresh()->billing_email);
    }

    public function test_stripe_email_returns_the_billing_email(): void
    {
        [$organization] = $this->createTenant();
        $organization->forceFill(['billing_email' => 'billing@example.com'])->save();

        $this->assertSame('billing@example.com', $organization->stripeEmail());
    }

    public function test_settings_update_does_not_attempt_to_sync_stripe_when_organization_has_no_stripe_id(): void
    {
        [$organization, $general] = $this->createTenant();
        $this->assertFalse($organization->hasStripeId());

        // stripe_idが無い組織は、Stripe API呼び出しに進まずに正常終了すること
        // (もし進んでしまうとテスト用の無効なAPIキーで例外になり、このテストが失敗する)。
        $this->actingAs($general)->put(route('settings.update'), [
            'name' => $organization->name,
            'billing_email' => 'billing@example.com',
        ])->assertRedirect(route('settings.edit'));
    }
}
