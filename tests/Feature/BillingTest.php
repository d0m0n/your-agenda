<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 14日間の無料トライアル(カード登録不要)とペイウォールのテスト。
 * Cashierのcheckout()は実際にStripe APIを呼ぶため、決済完了までのE2Eは
 * 自動テスト化していない(README記載のテストモードAPIキーでの手動確認手順)。
 */
class BillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_starts_a_fourteen_day_trial_without_requiring_a_card(): void
    {
        $this->post('/register', [
            'organization_name' => 'トライアル組織',
            'name' => 'Test User',
            'email' => 'trial@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $organization = Organization::where('name', 'トライアル組織')->firstOrFail();

        $this->assertNotNull($organization->trial_ends_at);
        $this->assertTrue($organization->trial_ends_at->betweenIncluded(now()->addDays(13), now()->addDays(15)));
        $this->assertTrue($organization->hasActiveAccess());
    }

    public function test_organization_within_trial_can_access_protected_pages(): void
    {
        $organization = Organization::factory()->create();
        $general = User::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->get(route('dashboard'))->assertOk();
    }

    public function test_general_user_of_expired_trial_organization_is_redirected_to_paywall(): void
    {
        $organization = Organization::factory()->expiredTrial()->create();
        $general = User::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->get(route('dashboard'))->assertRedirect(route('billing.paywall'));
        // meetings.index/showは今回の変更で閲覧専用に開放されたため、
        // 引き続きブロックされる別のルート(materials.index)で確認する。
        $this->actingAs($general)->get(route('materials.index'))->assertRedirect(route('billing.paywall'));
    }

    public function test_observer_of_expired_trial_organization_is_also_redirected_to_paywall(): void
    {
        $organization = Organization::factory()->expiredTrial()->create();
        $observer = User::factory()->for($organization, 'organization')->observer()->create();

        $this->actingAs($observer)->get(route('dashboard'))->assertRedirect(route('billing.paywall'));
    }

    public function test_paywall_page_itself_is_reachable_without_a_redirect_loop(): void
    {
        $organization = Organization::factory()->expiredTrial()->create();
        $general = User::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->get(route('billing.paywall'))
            ->assertOk()
            ->assertSee(__('お支払い情報を登録して利用を再開する'));
    }

    public function test_observer_sees_a_request_general_user_message_instead_of_a_payment_button(): void
    {
        $organization = Organization::factory()->expiredTrial()->create();
        $observer = User::factory()->for($organization, 'organization')->observer()->create();

        $this->actingAs($observer)->get(route('billing.paywall'))
            ->assertOk()
            ->assertDontSee(__('お支払い情報を登録して利用を再開する'))
            ->assertSee(__('お支払い手続きは、貴組織の一般ユーザーの方にご依頼ください。'));
    }

    public function test_data_export_remains_accessible_after_trial_expires(): void
    {
        $organization = Organization::factory()->expiredTrial()->create();
        $general = User::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->get(route('settings.export'))->assertOk();
    }

    /**
     * 未契約(トライアル終了)状態でも、困っていることを問い合わせられる
     * 必要があるため、inquiries.storeはsubscribedミドルウェアの対象外にして
     * いる。以前は対象内になっており、送信するとフォームの中身が何も
     * 案内されないままペイウォールへ差し戻され、問い合わせ自体が保存
     * されない不具合があった。
     */
    public function test_general_user_of_expired_trial_organization_can_still_submit_an_inquiry(): void
    {
        $organization = Organization::factory()->expiredTrial()->create();
        $general = User::factory()->for($organization, 'organization')->create();

        $response = $this->actingAs($general)->post(route('inquiries.store'), [
            'category' => 'inquiry',
            'subject' => '未契約状態からの問い合わせ',
            'body' => '本文です。',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('inquiries', [
            'organization_id' => $organization->id,
            'subject' => '未契約状態からの問い合わせ',
        ]);
    }

    public function test_observer_of_expired_trial_organization_can_still_submit_an_inquiry(): void
    {
        $organization = Organization::factory()->expiredTrial()->create();
        $observer = User::factory()->for($organization, 'organization')->observer()->create();

        $response = $this->actingAs($observer)->post(route('inquiries.store'), [
            'category' => 'bug',
            'subject' => 'オブザーブからの不具合報告',
            'body' => '本文です。',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('inquiries', [
            'organization_id' => $organization->id,
            'subject' => 'オブザーブからの不具合報告',
        ]);
    }

    public function test_observer_cannot_start_checkout(): void
    {
        $organization = Organization::factory()->expiredTrial()->create();
        $observer = User::factory()->for($organization, 'organization')->observer()->create();

        $this->actingAs($observer)->post(route('billing.checkout'))->assertForbidden();
    }

    /**
     * Stripe Checkoutは「このお客様はすでに契約中か」を見てくれないため、
     * ガード無しで連打すると同じ組織に並行したサブスクリプションが
     * 複数作られてしまう(実際にサンドボックスで発生した)。
     */
    public function test_already_subscribed_organization_cannot_start_a_second_checkout(): void
    {
        $organization = Organization::factory()->create();
        $general = User::factory()->for($organization, 'organization')->create();
        $organization->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_existing123',
            'stripe_status' => 'active',
            'stripe_price' => 'price_test',
            'quantity' => 1,
        ]);

        $this->actingAs($general)->post(route('billing.checkout'))
            ->assertRedirect(route('settings.edit'));

        $this->assertSame(1, $organization->subscriptions()->count());
    }

    public function test_already_subscribed_organization_is_sent_to_settings_instead_of_the_paywall(): void
    {
        $organization = Organization::factory()->create();
        $general = User::factory()->for($organization, 'organization')->create();
        $organization->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_existing456',
            'stripe_status' => 'active',
            'stripe_price' => 'price_test',
            'quantity' => 1,
        ]);

        $this->actingAs($general)->get(route('billing.paywall'))
            ->assertRedirect(route('settings.edit'));
    }

    public function test_super_admin_is_unaffected_by_any_organizations_trial_state(): void
    {
        $admin = User::factory()->create([
            'organization_id' => null,
            'role' => UserRole::SuperAdmin,
        ]);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }
}
