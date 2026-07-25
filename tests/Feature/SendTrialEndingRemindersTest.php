<?php

namespace Tests\Feature;

use App\Mail\TrialEndingSoonMail;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendTrialEndingRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_a_reminder_to_the_general_user_when_trial_ends_in_three_days(): void
    {
        Mail::fake();

        $organization = Organization::factory()->create(['trial_ends_at' => now()->addDays(3)->addHour()]);
        $general = User::factory()->for($organization, 'organization')->create();
        $observer = User::factory()->for($organization, 'organization')->observer()->create();

        $this->artisan('trials:send-ending-reminders')->assertSuccessful();

        Mail::assertSent(TrialEndingSoonMail::class, function (TrialEndingSoonMail $mail) use ($organization, $general) {
            return $mail->organization->is($organization)
                && $mail->hasTo($general->email);
        });
        Mail::assertNotSent(TrialEndingSoonMail::class, fn (TrialEndingSoonMail $mail) => $mail->hasTo($observer->email));

        $this->assertNotNull($organization->fresh()->trial_ending_reminder_sent_at);
    }

    public function test_does_not_send_when_trial_does_not_end_in_exactly_three_days(): void
    {
        Mail::fake();

        $soon = Organization::factory()->create(['trial_ends_at' => now()->addDays(2)->addHour()]);
        User::factory()->for($soon, 'organization')->create();

        $later = Organization::factory()->create(['trial_ends_at' => now()->addDays(10)]);
        User::factory()->for($later, 'organization')->create();

        $this->artisan('trials:send-ending-reminders');

        Mail::assertNothingSent();
    }

    public function test_does_not_send_twice_for_the_same_trial(): void
    {
        Mail::fake();

        $organization = Organization::factory()->create([
            'trial_ends_at' => now()->addDays(3)->addHour(),
            'trial_ending_reminder_sent_at' => now()->subDay(),
        ]);
        User::factory()->for($organization, 'organization')->create();

        $this->artisan('trials:send-ending-reminders');

        Mail::assertNothingSent();
    }

    public function test_does_not_send_to_organizations_with_free_access_enabled(): void
    {
        Mail::fake();

        $organization = Organization::factory()->create(['trial_ends_at' => now()->addDays(3)->addHour()]);
        $organization->forceFill(['free_access_enabled' => true])->save();
        User::factory()->for($organization, 'organization')->create();

        $this->artisan('trials:send-ending-reminders');

        Mail::assertNothingSent();
    }

    public function test_does_not_send_to_organizations_that_already_subscribed_before_trial_ended(): void
    {
        Mail::fake();

        $organization = Organization::factory()->create(['trial_ends_at' => now()->addDays(3)->addHour()]);
        $organization->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_test123',
            'stripe_status' => 'active',
            'stripe_price' => config('billing.monthly_price_id') ?? 'price_test',
            'quantity' => 1,
        ]);
        User::factory()->for($organization, 'organization')->create();

        $this->artisan('trials:send-ending-reminders');

        Mail::assertNothingSent();
    }
}
