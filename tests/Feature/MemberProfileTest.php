<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class MemberProfileTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_member_profile_page_displays_all_fields(): void
    {
        [$organization, $general] = $this->createTenant();
        $position = Position::factory()->for($organization, 'organization')->create(['name' => '理事長']);

        $member = Member::factory()->for($organization, 'organization')->create([
            'position_id' => $position->id,
            'serial_number' => 7,
            'name' => '山田太郎',
            'name_kana' => 'ヤマダタロウ',
            'name_romaji' => 'Taro Yamada',
            'birth_date' => '1990-05-14',
            'gender' => 'male',
            'company' => '株式会社サンプル',
            'phone' => '090-1234-5678',
            'email' => 'taro@example.com',
            'line_id' => 'taro_line',
            'x_account' => '@taro_x',
            'instagram_account' => '@taro_insta',
            'facebook_account' => 'taro.facebook',
            'tiktok_account' => '@taro_tiktok',
            'hobby' => '登山',
            'motto' => '継続は力なり',
        ]);

        $response = $this->actingAs($general)->get(route('members.show', $member));

        $response->assertOk();
        $response->assertSee('山田太郎');
        $response->assertSee('ヤマダタロウ');
        $response->assertSee('Taro Yamada');
        $response->assertSee('理事長');
        $response->assertSee('株式会社サンプル');
        $response->assertSee('090-1234-5678');
        $response->assertSee('taro@example.com');
        $response->assertSee('taro_line');
        $response->assertSee('@taro_x');
        $response->assertSee('@taro_insta');
        $response->assertSee('taro.facebook');
        $response->assertSee('@taro_tiktok');
        $response->assertSee('登山');
        $response->assertSee('継続は力なり');
        $response->assertSee('No. 007', false);
        $response->assertSee('1990年5月14日');
    }

    public function test_member_profile_page_omits_empty_sections_gracefully(): void
    {
        [$organization, $general] = $this->createTenant();

        $member = Member::factory()->for($organization, 'organization')->create([
            'name' => '名前のみ太郎',
            'name_kana' => null,
            'company' => null,
            'phone' => null,
            'email' => null,
        ]);

        $response = $this->actingAs($general)->get(route('members.show', $member));

        $response->assertOk();
        $response->assertSee('名前のみ太郎');
        $response->assertDontSee('SNS');
    }

    public function test_observer_can_view_member_profile(): void
    {
        [$organization, , $observer] = $this->createTenant();

        $member = Member::factory()->for($organization, 'organization')->create(['name' => '観覧対象太郎']);

        $response = $this->actingAs($observer)->get(route('members.show', $member));

        $response->assertOk();
        $response->assertSee('観覧対象太郎');
    }

    public function test_member_index_links_to_profile_page(): void
    {
        [$organization, $general] = $this->createTenant();

        $member = Member::factory()->for($organization, 'organization')->create(['name' => 'リンク太郎']);

        $response = $this->actingAs($general)->get(route('members.index'));

        $response->assertOk();
        $response->assertSee(route('members.show', $member), false);
    }

    public function test_member_index_shows_card_grid_alongside_table(): void
    {
        [$organization, $general] = $this->createTenant();

        Member::factory()->for($organization, 'organization')->create(['name' => 'カード太郎']);

        $response = $this->actingAs($general)->get(route('members.index'));

        $response->assertOk();
        $response->assertSee(__('表形式'));
        $response->assertSee(__('カード形式'));
    }

    public function test_member_profile_page_links_to_adjacent_members_in_name_order(): void
    {
        [$organization, $general] = $this->createTenant();

        $first = Member::factory()->for($organization, 'organization')->create(['name' => 'あ田一郎']);
        $middle = Member::factory()->for($organization, 'organization')->create(['name' => 'い田二郎']);
        $last = Member::factory()->for($organization, 'organization')->create(['name' => 'う田三郎']);

        $response = $this->actingAs($general)->get(route('members.show', $middle));

        $response->assertOk();
        $response->assertSee(route('members.show', $first), false);
        $response->assertSee(route('members.show', $last), false);
    }

    public function test_member_profile_page_omits_previous_link_for_first_member(): void
    {
        [$organization, $general] = $this->createTenant();

        $first = Member::factory()->for($organization, 'organization')->create(['name' => 'あ田一郎']);
        $second = Member::factory()->for($organization, 'organization')->create(['name' => 'い田二郎']);

        $response = $this->actingAs($general)->get(route('members.show', $first));

        $response->assertOk();
        $response->assertDontSee(__('← 前のメンバー'));
        $response->assertSee(route('members.show', $second), false);
    }

    public function test_member_profile_page_omits_next_link_for_last_member(): void
    {
        [$organization, $general] = $this->createTenant();

        $first = Member::factory()->for($organization, 'organization')->create(['name' => 'あ田一郎']);
        $last = Member::factory()->for($organization, 'organization')->create(['name' => 'い田二郎']);

        $response = $this->actingAs($general)->get(route('members.show', $last));

        $response->assertOk();
        $response->assertDontSee(__('次のメンバー →'));
        $response->assertSee(route('members.show', $first), false);
    }
}
