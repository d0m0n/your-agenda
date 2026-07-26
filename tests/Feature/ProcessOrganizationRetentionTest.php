<?php

namespace Tests\Feature;

use App\Mail\AccountScheduledForDeletion;
use App\Mail\OrganizationAutomaticallyDeleted;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * トライアル終了・解約でアクセス権を失った組織の猶予期間管理・自動削除
 * (app/Console/Commands/ProcessOrganizationRetention.php)。
 */
class ProcessOrganizationRetentionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['error_alerts.mail_to' => 'admin@example.com']);
    }

    public function test_starts_the_grace_period_for_an_organization_that_just_lost_access(): void
    {
        Mail::fake();

        $organization = Organization::factory()->expiredTrial()->create();
        $general = User::factory()->for($organization, 'organization')->create();

        $this->artisan('organizations:process-retention')->assertSuccessful();

        $this->assertNotNull($organization->fresh()->access_lost_at);
        Mail::assertSent(AccountScheduledForDeletion::class, fn ($mail) => $mail->hasTo($general->email));
    }

    public function test_does_not_start_the_grace_period_while_access_is_still_active(): void
    {
        Mail::fake();

        // デフォルトのファクトリはトライアル中(遠い未来のtrial_ends_at)。
        Organization::factory()->create();

        $this->artisan('organizations:process-retention')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_does_not_start_the_grace_period_for_a_free_access_organization(): void
    {
        Mail::fake();

        Organization::factory()->expiredTrial()->create(['free_access_enabled' => true]);

        $this->artisan('organizations:process-retention')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_clears_access_lost_at_when_access_is_restored(): void
    {
        $organization = Organization::factory()->expiredTrial()->create([
            'access_lost_at' => now()->subDays(10),
            'free_access_enabled' => true,
        ]);

        $this->artisan('organizations:process-retention')->assertSuccessful();

        $this->assertNull($organization->fresh()->access_lost_at);
    }

    public function test_deletes_an_organization_once_the_grace_period_has_expired(): void
    {
        Mail::fake();

        $organization = Organization::factory()->expiredTrial()->create([
            'access_lost_at' => now()->subDays(91),
        ]);

        $this->artisan('organizations:process-retention')->assertSuccessful();

        $this->assertDatabaseMissing('organizations', ['id' => $organization->id]);
        Mail::assertSent(OrganizationAutomaticallyDeleted::class, fn ($mail) => $mail->hasTo('admin@example.com')
            && $mail->organizationId === $organization->id);
    }

    public function test_does_not_delete_an_organization_still_within_the_grace_period(): void
    {
        $organization = Organization::factory()->expiredTrial()->create([
            'access_lost_at' => now()->subDays(10),
        ]);

        $this->artisan('organizations:process-retention')->assertSuccessful();

        $this->assertDatabaseHas('organizations', ['id' => $organization->id]);
    }
}
