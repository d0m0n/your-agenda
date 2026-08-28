<?php

namespace Tests\Feature;

use App\Enums\OrganizationPlan;
use App\Exceptions\MinutesGenerationException;
use App\Models\Meeting;
use App\Services\ClaudeMinutesGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

/**
 * AI議事録生成(プラスプラン限定)。実際のClaude APIは呼ばず、
 * ClaudeMinutesGeneratorをモックしてルーティング・権限・永続化のみを
 * 検証する(BillingTest.phpが実Stripe APIを呼ぶ経路を自動テスト対象外に
 * しているのと同じ方針)。
 */
class MeetingMinutesTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    private function makeSubscribedOrganization(OrganizationPlan $plan): array
    {
        [$organization, $general, $observer] = $this->createTenant();
        $organization->forceFill(['plan' => $plan, 'trial_ends_at' => now()->subDay()])->save();
        $organization->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_test_'.uniqid(),
            'stripe_status' => 'active',
        ]);

        return [$organization->fresh(), $general, $observer];
    }

    public function test_plus_organization_general_user_can_view_the_minutes_edit_page(): void
    {
        [, $general] = $this->makeSubscribedOrganization(OrganizationPlan::Plus);
        $meeting = Meeting::factory()->for($general->organization, 'organization')->create();

        $this->actingAs($general)->get(route('meetings.minutes.edit', $meeting))->assertOk();
    }

    public function test_standard_plan_organization_is_forbidden(): void
    {
        [, $general] = $this->makeSubscribedOrganization(OrganizationPlan::Standard);
        $meeting = Meeting::factory()->for($general->organization, 'organization')->create();

        $this->actingAs($general)->get(route('meetings.minutes.edit', $meeting))->assertForbidden();
    }

    public function test_unsubscribed_organization_is_redirected_to_the_paywall(): void
    {
        [$organization, $general] = $this->createTenant();
        $organization->forceFill(['plan' => OrganizationPlan::Plus, 'trial_ends_at' => now()->subDay()])->save();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->get(route('meetings.minutes.edit', $meeting))
            ->assertRedirect(route('billing.paywall'));
    }

    public function test_observer_cannot_access_any_minutes_route(): void
    {
        [, , $observer] = $this->makeSubscribedOrganization(OrganizationPlan::Plus);
        $meeting = Meeting::factory()->for($observer->organization, 'organization')->create();

        $this->actingAs($observer)->get(route('meetings.minutes.edit', $meeting))->assertForbidden();
        $this->actingAs($observer)->post(route('meetings.minutes.generate', $meeting), ['transcript' => 'x'])->assertForbidden();
    }

    public function test_generating_minutes_saves_transcript_body_and_timestamp(): void
    {
        [, $general] = $this->makeSubscribedOrganization(OrganizationPlan::Plus);
        $meeting = Meeting::factory()->for($general->organization, 'organization')->create();

        $this->mock(ClaudeMinutesGenerator::class, function ($mock) {
            $mock->shouldReceive('generate')->once()->andReturn('生成された議事録テキスト');
        });

        $response = $this->actingAs($general)->post(route('meetings.minutes.generate', $meeting), [
            'transcript' => '会議の文字起こしテキストです。',
        ]);

        $response->assertRedirect(route('meetings.minutes.edit', $meeting));

        $meeting->refresh();
        $this->assertSame('会議の文字起こしテキストです。', $meeting->minutes_transcript);
        $this->assertSame('生成された議事録テキスト', $meeting->minutes_body);
        $this->assertNotNull($meeting->minutes_generated_at);
    }

    public function test_generation_failure_redisplays_the_pasted_transcript_with_an_error(): void
    {
        [, $general] = $this->makeSubscribedOrganization(OrganizationPlan::Plus);
        $meeting = Meeting::factory()->for($general->organization, 'organization')->create();

        $this->mock(ClaudeMinutesGenerator::class, function ($mock) {
            $mock->shouldReceive('generate')->once()->andThrow(
                new MinutesGenerationException('議事録の生成に失敗しました。しばらく待ってから再度お試しください。')
            );
        });

        $response = $this->actingAs($general)->from(route('meetings.minutes.edit', $meeting))->post(
            route('meetings.minutes.generate', $meeting),
            ['transcript' => '失敗するはずの文字起こし']
        );

        $response->assertRedirect(route('meetings.minutes.edit', $meeting));
        $response->assertSessionHasErrors('transcript');
        $response->assertSessionHas('_old_input.transcript', '失敗するはずの文字起こし');

        $this->assertNull($meeting->fresh()->minutes_body);
    }

    public function test_manual_edit_of_generated_draft_is_saved_via_update(): void
    {
        [, $general] = $this->makeSubscribedOrganization(OrganizationPlan::Plus);
        $meeting = Meeting::factory()->for($general->organization, 'organization')->create([
            'minutes_body' => '元の議事録',
        ]);

        $response = $this->actingAs($general)->put(route('meetings.minutes.update', $meeting), [
            'body' => '編集後の議事録',
        ]);

        $response->assertRedirect(route('meetings.minutes.edit', $meeting));
        $this->assertSame('編集後の議事録', $meeting->fresh()->minutes_body);
    }

    public function test_pdf_view_returns_404_when_no_minutes_have_been_generated_yet(): void
    {
        [, $general] = $this->makeSubscribedOrganization(OrganizationPlan::Plus);
        $meeting = Meeting::factory()->for($general->organization, 'organization')->create();

        $this->actingAs($general)->get(route('meetings.minutes.pdf', $meeting))->assertNotFound();
    }

    public function test_pdf_view_is_shown_once_minutes_exist(): void
    {
        [, $general] = $this->makeSubscribedOrganization(OrganizationPlan::Plus);
        $meeting = Meeting::factory()->for($general->organization, 'organization')->create([
            'minutes_body' => '議事録本文',
        ]);

        $this->actingAs($general)->get(route('meetings.minutes.pdf', $meeting))
            ->assertOk()
            ->assertSee('議事録本文');
    }
}
