<?php

namespace Tests\Feature;

use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class MemberManagementTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_creating_a_member_without_a_serial_number_fails_validation(): void
    {
        [, $general] = $this->createTenant();

        $response = $this->actingAs($general)->post(route('members.store'), [
            'name' => '新規メンバー',
        ]);

        $response->assertSessionHasErrors('serial_number');
        $this->assertDatabaseMissing('members', ['name' => '新規メンバー']);
    }

    public function test_updating_a_member_without_a_serial_number_fails_validation(): void
    {
        [$organization, $general] = $this->createTenant();
        $member = Member::factory()->for($organization, 'organization')->create(['serial_number' => 3]);

        $response = $this->actingAs($general)->put(route('members.update', $member), [
            'name' => $member->name,
        ]);

        $response->assertSessionHasErrors('serial_number');
        $this->assertSame(3, $member->fresh()->serial_number);
    }

    public function test_create_form_suggests_the_next_available_serial_number(): void
    {
        [$organization, $general] = $this->createTenant();
        Member::factory()->for($organization, 'organization')->create(['serial_number' => 1]);
        Member::factory()->for($organization, 'organization')->create(['serial_number' => 2]);

        $this->actingAs($general)->get(route('members.create'))
            ->assertOk()
            ->assertSee('value="3"', false);
    }

    public function test_edit_form_suggests_the_next_available_serial_number_when_missing(): void
    {
        [$organization, $general] = $this->createTenant();
        Member::factory()->for($organization, 'organization')->create(['serial_number' => 1]);
        $memberWithoutNumber = Member::factory()->for($organization, 'organization')->create(['serial_number' => null]);

        $this->actingAs($general)->get(route('members.edit', $memberWithoutNumber))
            ->assertOk()
            ->assertSee('value="2"', false);
    }

    public function test_member_index_defaults_to_sorting_by_serial_number(): void
    {
        [$organization, $general] = $this->createTenant();
        Member::factory()->for($organization, 'organization')->create(['name' => 'Bさん', 'serial_number' => 2]);
        Member::factory()->for($organization, 'organization')->create(['name' => 'Aさん', 'serial_number' => 1]);

        $response = $this->actingAs($general)->get(route('members.index'));

        $response->assertOk();
        $content = $response->getContent();
        $this->assertLessThan(
            strpos($content, 'Bさん'),
            strpos($content, 'Aさん'),
            '通し番号順(1が先、2が後)で表示されていること'
        );
    }
}
