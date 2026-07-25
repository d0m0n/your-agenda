<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_observer_cannot_access_the_billing_portal(): void
    {
        $organization = Organization::factory()->create();
        $observer = User::factory()->for($organization, 'organization')->observer()->create();

        $this->actingAs($observer)->post(route('billing.portal'))->assertForbidden();
    }

    public function test_portal_button_is_hidden_until_the_organization_has_a_stripe_customer(): void
    {
        $organization = Organization::factory()->create();
        $general = User::factory()->for($organization, 'organization')->create();

        // ボタンの有無はフォームの送信先URL(billing.portal)でピンポイントに
        // 判定する(ボタンの文言と説明文の文言が部分一致してしまうため)。
        $this->actingAs($general)->get(route('settings.edit'))
            ->assertOk()
            ->assertDontSee(route('billing.portal'), false)
            ->assertSee(__('お支払い情報を一度ご登録いただくと、こちらからお支払い方法の変更・請求書の確認・解約ができるようになります。'));
    }

    public function test_portal_button_is_shown_once_the_organization_has_a_stripe_customer(): void
    {
        $organization = Organization::factory()->create();
        $organization->forceFill(['stripe_id' => 'cus_test123'])->save();
        $general = User::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->get(route('settings.edit'))
            ->assertOk()
            ->assertSee(route('billing.portal'), false);
    }
}
