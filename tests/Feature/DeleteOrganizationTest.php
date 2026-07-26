<?php

namespace Tests\Feature;

use App\Models\AgendaItem;
use App\Models\Department;
use App\Models\Inquiry;
use App\Models\Material;
use App\Models\Meeting;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Position;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

/**
 * 退会依頼への対応(組織の完全削除)コマンド。UIからの操作経路はなく、
 * サーバー上でのartisan実行のみで行う運用(app/Console/Commands/
 * DeleteOrganization.php参照)。
 */
class DeleteOrganizationTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_fails_for_a_nonexistent_organization(): void
    {
        $this->artisan('organizations:delete', ['organization' => 999999, '--force' => true])
            ->assertExitCode(1);
    }

    public function test_force_deletes_the_organization_and_cascades_related_records(): void
    {
        [$organization, $general, $observer] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        $item = AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '議案']);
        $member = Member::factory()->for($organization, 'organization')->create();
        $position = Position::factory()->for($organization, 'organization')->create();
        $department = Department::factory()->for($organization, 'organization')->create();
        $material = Material::factory()->for($organization, 'organization')->for($general, 'user')->create();
        $site = Site::factory()->for($organization, 'organization')->create();
        $inquiry = Inquiry::factory()->for($organization, 'organization')->for($general, 'user')->create();

        $this->artisan('organizations:delete', ['organization' => $organization->id, '--force' => true])
            ->assertExitCode(0);

        $this->assertDatabaseMissing('organizations', ['id' => $organization->id]);
        $this->assertDatabaseMissing('users', ['id' => $general->id]);
        $this->assertDatabaseMissing('users', ['id' => $observer->id]);
        $this->assertDatabaseMissing('meetings', ['id' => $meeting->id]);
        $this->assertDatabaseMissing('agenda_items', ['id' => $item->id]);
        $this->assertDatabaseMissing('members', ['id' => $member->id]);
        $this->assertDatabaseMissing('positions', ['id' => $position->id]);
        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
        $this->assertDatabaseMissing('materials', ['id' => $material->id]);
        $this->assertDatabaseMissing('sites', ['id' => $site->id]);
        $this->assertDatabaseMissing('inquiries', ['id' => $inquiry->id]);
    }

    public function test_without_force_requires_typing_the_exact_organization_name(): void
    {
        $organization = Organization::factory()->create(['name' => '削除テスト組織']);

        $this->artisan('organizations:delete', ['organization' => $organization->id])
            ->expectsQuestion('削除を実行するには、組織名を正確に入力してください', '違う名前')
            ->assertExitCode(1);

        $this->assertDatabaseHas('organizations', ['id' => $organization->id]);
    }

    public function test_without_force_proceeds_when_the_typed_name_matches(): void
    {
        $organization = Organization::factory()->create(['name' => '削除テスト組織']);

        $this->artisan('organizations:delete', ['organization' => $organization->id])
            ->expectsQuestion('削除を実行するには、組織名を正確に入力してください', '削除テスト組織')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('organizations', ['id' => $organization->id]);
    }

    public function test_deletes_local_subscription_rows_which_have_no_cascade(): void
    {
        $organization = Organization::factory()->create();
        $organization->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_test_'.uniqid(),
            'stripe_status' => 'active',
        ]);

        $this->artisan('organizations:delete', ['organization' => $organization->id, '--force' => true])
            ->assertExitCode(0);

        $this->assertDatabaseMissing('subscriptions', ['organization_id' => $organization->id]);
    }
}
