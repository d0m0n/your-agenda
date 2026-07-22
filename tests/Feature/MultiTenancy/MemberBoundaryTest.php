<?php

namespace Tests\Feature\MultiTenancy;

use App\Models\Member;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class MemberBoundaryTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_general_user_cannot_view_edit_form_of_another_organizations_member(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $memberB = Member::factory()->for($orgB, 'organization')->create();

        $this->actingAs($userA)
            ->get(route('members.edit', $memberB))
            ->assertNotFound();
    }

    public function test_general_user_cannot_update_another_organizations_member(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $memberB = Member::factory()->for($orgB, 'organization')->create(['name' => '田中太郎']);

        $this->actingAs($userA)
            ->put(route('members.update', $memberB), ['name' => '書き換え太郎'])
            ->assertNotFound();

        $this->assertSame('田中太郎', $memberB->fresh()->name);
    }

    public function test_general_user_cannot_delete_another_organizations_member(): void
    {
        [, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $memberB = Member::factory()->for($orgB, 'organization')->create();

        $this->actingAs($userA)
            ->delete(route('members.destroy', $memberB))
            ->assertNotFound();

        $this->assertModelExists($memberB);
    }

    public function test_member_index_does_not_list_other_organizations_members(): void
    {
        [$orgA, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        Member::factory()->for($orgA, 'organization')->create(['name' => '自組織メンバー']);
        Member::factory()->for($orgB, 'organization')->create(['name' => '他組織メンバー']);

        $response = $this->actingAs($userA)->get(route('members.index'));

        $response->assertOk();
        $response->assertSee('自組織メンバー');
        $response->assertDontSee('他組織メンバー');
    }

    public function test_member_csv_export_only_includes_own_organizations_members(): void
    {
        [$orgA, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        Member::factory()->for($orgA, 'organization')->create(['name' => '自組織エクスポート対象']);
        Member::factory()->for($orgB, 'organization')->create(['name' => '他組織エクスポート対象外']);

        $response = $this->actingAs($userA)->get(route('members.export'));

        $response->assertOk();
        $csv = $response->getContent();
        $this->assertStringContainsString('自組織エクスポート対象', $csv);
        $this->assertStringNotContainsString('他組織エクスポート対象外', $csv);
    }

    public function test_member_cannot_be_assigned_a_position_belonging_to_another_organization(): void
    {
        [$orgA, $userA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        $memberA = Member::factory()->for($orgA, 'organization')->create();
        $positionB = Position::factory()->for($orgB, 'organization')->create();

        $response = $this->actingAs($userA)
            ->put(route('members.update', $memberA), [
                'name' => $memberA->name,
                'position_id' => $positionB->id,
            ]);

        $response->assertSessionHasErrors('position_id');
        $this->assertNull($memberA->fresh()->position_id);
    }

    public function test_observer_cannot_access_member_management_routes(): void
    {
        [$orgA, , $observerA] = $this->createTenant();

        $member = Member::factory()->for($orgA, 'organization')->create();

        $this->actingAs($observerA)->get(route('members.create'))->assertForbidden();
        $this->actingAs($observerA)->get(route('members.edit', $member))->assertForbidden();
        $this->actingAs($observerA)->put(route('members.update', $member), ['name' => '不正更新'])->assertForbidden();
        $this->actingAs($observerA)->delete(route('members.destroy', $member))->assertForbidden();
        $this->actingAs($observerA)->get(route('members.export'))->assertForbidden();
        $this->actingAs($observerA)->get(route('members.csv-template'))->assertForbidden();

        $this->assertModelExists($member);
    }

    public function test_observer_can_view_member_index_but_not_management_links(): void
    {
        [$orgA, , $observerA] = $this->createTenant();

        Member::factory()->for($orgA, 'organization')->create(['name' => '自組織メンバー']);

        $response = $this->actingAs($observerA)->get(route('members.index'));

        $response->assertOk();
        $response->assertSee('自組織メンバー');
        $response->assertDontSee(route('members.create'), false);
        $response->assertDontSee(route('members.export'), false);
    }

    public function test_member_index_does_not_list_other_organizations_members_for_observer(): void
    {
        [$orgA, , $observerA] = $this->createTenant();
        [$orgB] = $this->createTenant();

        Member::factory()->for($orgA, 'organization')->create(['name' => '自組織メンバー']);
        Member::factory()->for($orgB, 'organization')->create(['name' => '他組織メンバー']);

        $response = $this->actingAs($observerA)->get(route('members.index'));

        $response->assertOk();
        $response->assertSee('自組織メンバー');
        $response->assertDontSee('他組織メンバー');
    }
}
