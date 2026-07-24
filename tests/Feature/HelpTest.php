<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class HelpTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_general_user_sees_the_general_manual(): void
    {
        [, $general] = $this->createTenant();

        $response = $this->actingAs($general)->get(route('help.index'));

        $response->assertOk();
        $response->assertSee('一般ユーザー向けに');
        $response->assertSee('オブザーブユーザーの管理');
        $response->assertDontSee('オブザーブユーザーができないこと');
    }

    public function test_observer_user_sees_the_observer_manual(): void
    {
        [, , $observer] = $this->createTenant();

        $response = $this->actingAs($observer)->get(route('help.index'));

        $response->assertOk();
        $response->assertSee('オブザーブユーザー(閲覧専用アカウント)向けに');
        $response->assertSee('オブザーブユーザーができないこと');
        $response->assertDontSee('案内文作成');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('help.index'))->assertRedirect(route('login'));
    }
}
