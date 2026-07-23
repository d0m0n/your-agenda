<?php

namespace Tests\Feature;

use App\Models\AgendaItem;
use App\Models\Meeting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class MeetingInvitationTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_edit_page_shows_generated_template_by_default(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create([
            'name' => '7月定例会',
            'location' => '市民会館',
        ]);
        AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '開会宣言']);

        $response = $this->actingAs($general)->get(route('meetings.invitation.edit', $meeting));

        $response->assertOk();
        $response->assertSee('7月定例会');
        $response->assertSee('市民会館');
        $response->assertSee('開会宣言');
        $response->assertSee('拝啓'); // pdf template marker
        $response->assertSee('件名: 【ご案内】7月定例会'); // email template marker
        $response->assertSee('【7月定例会のご案内】'); // line template marker
    }

    public function test_manager_can_save_custom_invitation_text(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $response = $this->actingAs($general)->put(route('meetings.invitation.update', $meeting), [
            'type' => 'email',
            'body' => '手直しした本文です。',
        ]);

        $response->assertRedirect(route('meetings.invitation.edit', $meeting));
        $this->assertSame('手直しした本文です。', $meeting->fresh()->invitation_email_body);
    }

    public function test_saved_custom_text_is_shown_instead_of_the_template(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->put(route('meetings.invitation.update', $meeting), [
            'type' => 'line',
            'body' => '手直ししたLINE本文です。',
        ]);

        $response = $this->actingAs($general)->get(route('meetings.invitation.edit', $meeting));

        $response->assertOk();
        $response->assertSee('手直ししたLINE本文です。');
    }

    public function test_manager_can_reset_custom_text_back_to_the_template(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->put(route('meetings.invitation.update', $meeting), [
            'type' => 'email',
            'body' => '手直しした本文です。',
        ]);
        $this->assertNotNull($meeting->fresh()->invitation_email_body);

        $response = $this->actingAs($general)->delete(route('meetings.invitation.reset', [$meeting, 'email']));

        $response->assertRedirect(route('meetings.invitation.edit', $meeting));
        $this->assertNull($meeting->fresh()->invitation_email_body);
    }

    public function test_invalid_type_is_rejected_on_update(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $response = $this->actingAs($general)->put(route('meetings.invitation.update', $meeting), [
            'type' => 'sms',
            'body' => '不正な種類です。',
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_invalid_type_is_rejected_on_reset(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)
            ->delete(route('meetings.invitation.reset', [$meeting, 'sms']))
            ->assertNotFound();
    }

    public function test_observer_cannot_update_invitation_text(): void
    {
        [$organization, , $observer] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $this->actingAs($observer)
            ->put(route('meetings.invitation.update', $meeting), ['type' => 'email', 'body' => '不正な更新'])
            ->assertForbidden();

        $this->assertNull($meeting->fresh()->invitation_email_body);
    }

    public function test_observer_cannot_view_the_invitation_edit_page(): void
    {
        [$organization, , $observer] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $this->actingAs($observer)
            ->get(route('meetings.invitation.edit', $meeting))
            ->assertForbidden();
    }

    public function test_pdf_view_shows_the_template_when_no_custom_text_is_saved(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create(['name' => '7月定例会']);

        $response = $this->actingAs($general)->get(route('meetings.invitation.pdf', $meeting));

        $response->assertOk();
        $response->assertSee('拝啓');
        $response->assertSee('7月定例会');
    }

    public function test_pdf_view_shows_custom_text_when_saved(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->put(route('meetings.invitation.update', $meeting), [
            'type' => 'pdf',
            'body' => '手直ししたPDF案内文です。',
        ]);

        $response = $this->actingAs($general)->get(route('meetings.invitation.pdf', $meeting));

        $response->assertOk();
        $response->assertSee('手直ししたPDF案内文です。');
        $response->assertDontSee('拝啓');
    }

    public function test_pdf_view_shows_a_formal_letter_issue_date(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $response = $this->actingAs($general)->get(route('meetings.invitation.pdf', $meeting));

        $response->assertOk();
        $response->assertSee(now()->format('Y年n月').'吉日');
    }

    public function test_pdf_view_applies_letter_formatting_classes_to_recognized_lines(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create(['name' => '7月定例会']);

        $response = $this->actingAs($general)->get(route('meetings.invitation.pdf', $meeting));

        $response->assertOk();
        // built-in default template contains 記/敬具/以上 and a "...ご案内" title line
        $response->assertSee('<div class="whitespace-pre-wrap text-center tracking-[0.4em]">記</div>', false);
        $response->assertSee('<div class="whitespace-pre-wrap text-right">敬具</div>', false);
        $response->assertSee('<div class="whitespace-pre-wrap text-right">以上</div>', false);
        $response->assertSee('text-center font-semibold text-base underline underline-offset-4', false);
    }

    public function test_pdf_view_right_aligns_lines_marked_with_the_gt_gt_prefix(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $this->actingAs($general)->put(route('meetings.invitation.update', $meeting), [
            'type' => 'pdf',
            'body' => "本文\n>>差出人情報",
        ]);

        $response = $this->actingAs($general)->get(route('meetings.invitation.pdf', $meeting));

        $response->assertOk();
        $response->assertSee('<div class="whitespace-pre-wrap text-right">差出人情報</div>', false);
        $response->assertDontSee('>>差出人情報');
    }

    public function test_pdf_default_template_marks_the_sender_line_for_right_alignment(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        $response = $this->actingAs($general)->get(route('meetings.invitation.pdf', $meeting));

        $response->assertOk();
        $response->assertSee('メンバー各位');
        $response->assertDontSee('>>');
        // the sender line (second occurrence of the org name) is right-aligned
        $response->assertSee('<div class="whitespace-pre-wrap text-right">'.$organization->name.'</div>', false);
    }

    public function test_pdf_default_template_uses_a_seasonal_greeting_matching_the_current_month(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        Carbon::setTestNow('2026-11-15');

        try {
            $response = $this->actingAs($general)->get(route('meetings.invitation.pdf', $meeting));
        } finally {
            Carbon::setTestNow();
        }

        $response->assertOk();
        $response->assertSee('拝啓　晩秋の候、平素は格別のご高配を賜り、厚く御礼申し上げます。');
    }

    public function test_pdf_default_template_uses_a_different_seasonal_greeting_in_a_different_month(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create();

        Carbon::setTestNow('2026-01-05');

        try {
            $response = $this->actingAs($general)->get(route('meetings.invitation.pdf', $meeting));
        } finally {
            Carbon::setTestNow();
        }

        $response->assertOk();
        $response->assertSee('拝啓　新春の候、平素は格別のご高配を賜り、厚く御礼申し上げます。');
    }

    public function test_pdf_view_shows_a_qr_code_when_a_map_url_is_set(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create([
            'venue_map_url' => 'https://maps.google.com/?q=市民会館',
            'venue_address' => '東京都千代田区1-1-1',
        ]);

        $response = $this->actingAs($general)->get(route('meetings.invitation.pdf', $meeting));

        $response->assertOk();
        $response->assertSee('data:image/png;base64,', false);
        $response->assertSee('東京都千代田区1-1-1');
    }

    public function test_pdf_view_omits_the_qr_code_when_no_map_url_is_set(): void
    {
        [$organization, $general] = $this->createTenant();
        $meeting = Meeting::factory()->for($organization, 'organization')->create(['venue_map_url' => null, 'venue_address' => null]);

        $response = $this->actingAs($general)->get(route('meetings.invitation.pdf', $meeting));

        $response->assertOk();
        $response->assertDontSee('data:image/png;base64,', false);
    }

    public function test_venue_and_event_placeholders_are_substituted_via_the_organization_template(): void
    {
        [$organization, $general] = $this->createTenant();

        $this->actingAs($general)->put(route('settings.invitation-templates.update'), [
            'type' => 'pdf',
            'body' => "住所: {{住所}}\n地図: {{地図URL}}\n懇親会: {{懇親会情報}}\n宿泊: {{宿泊情報}}",
        ]);

        $meeting = Meeting::factory()->for($organization, 'organization')->create([
            'venue_address' => '東京都千代田区1-1-1',
            'venue_map_url' => 'https://maps.google.com/?q=test',
            'social_event_info' => '懇親会 19:30〜 会費3,000円',
            'recommended_hotel_info' => '〇〇ホテル(会場から徒歩5分)',
        ]);

        $response = $this->actingAs($general)->get(route('meetings.invitation.edit', $meeting));

        $response->assertOk();
        $response->assertSee('住所: 東京都千代田区1-1-1');
        $response->assertSee('地図: https://maps.google.com/?q=test', false);
        $response->assertSee('懇親会: 懇親会 19:30〜 会費3,000円');
        $response->assertSee('宿泊: 〇〇ホテル(会場から徒歩5分)');
    }
}
