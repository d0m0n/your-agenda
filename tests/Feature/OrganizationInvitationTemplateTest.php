<?php

namespace Tests\Feature;

use App\Models\AgendaItem;
use App\Models\Meeting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class OrganizationInvitationTemplateTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_settings_page_shows_the_built_in_default_template_when_unset(): void
    {
        [, $general] = $this->createTenant();

        $response = $this->actingAs($general)->get(route('settings.edit'));

        $response->assertOk();
        $response->assertSee('案内文のデフォルト');
        $response->assertSee('拝啓');
        $response->assertSee('{{会議名}}', false);
    }

    public function test_manager_can_save_a_custom_organization_template(): void
    {
        [$organization, $general] = $this->createTenant();

        $response = $this->actingAs($general)->put(route('settings.invitation-templates.update'), [
            'type' => 'email',
            'body' => 'カスタムテンプレート: {{会議名}} / {{開催日時}}',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $this->assertSame('カスタムテンプレート: {{会議名}} / {{開催日時}}', $organization->fresh()->invitation_email_template);
    }

    public function test_meeting_invitation_is_generated_from_the_organizations_custom_template(): void
    {
        [$organization, $general] = $this->createTenant();

        $this->actingAs($general)->put(route('settings.invitation-templates.update'), [
            'type' => 'email',
            'body' => "独自テンプレート\n会議名: {{会議名}}\n日時: {{開催日時}}",
        ]);

        $meeting = Meeting::factory()->for($organization, 'organization')->create(['name' => '7月定例会']);

        $response = $this->actingAs($general)->get(route('meetings.invitation.edit', $meeting));

        $response->assertOk();
        $response->assertSee('独自テンプレート');
        $response->assertSee('会議名: 7月定例会');
        $response->assertDontSee('件名: 【ご案内】'); // built-in default marker should be gone
    }

    public function test_manager_can_reset_the_organization_template_back_to_the_built_in_default(): void
    {
        [$organization, $general] = $this->createTenant();

        $this->actingAs($general)->put(route('settings.invitation-templates.update'), [
            'type' => 'pdf',
            'body' => 'カスタムPDFテンプレート',
        ]);
        $this->assertNotNull($organization->fresh()->invitation_pdf_template);

        $response = $this->actingAs($general)->delete(route('settings.invitation-templates.reset', 'pdf'));

        $response->assertRedirect(route('settings.edit'));
        $this->assertNull($organization->fresh()->invitation_pdf_template);
    }

    public function test_invalid_type_is_rejected(): void
    {
        [, $general] = $this->createTenant();

        $response = $this->actingAs($general)->put(route('settings.invitation-templates.update'), [
            'type' => 'sms',
            'body' => '不正な種類です。',
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_observer_cannot_update_the_organization_template(): void
    {
        [$organization, , $observer] = $this->createTenant();

        $this->actingAs($observer)
            ->put(route('settings.invitation-templates.update'), ['type' => 'email', 'body' => '不正な更新'])
            ->assertForbidden();

        $this->assertNull($organization->fresh()->invitation_email_template);
    }

    public function test_agenda_placeholder_is_replaced_with_the_meetings_own_agenda_items(): void
    {
        [$organization, $general] = $this->createTenant();

        $this->actingAs($general)->put(route('settings.invitation-templates.update'), [
            'type' => 'line',
            'body' => '議題一覧: {{議題}}',
        ]);

        $meeting = Meeting::factory()->for($organization, 'organization')->create();
        AgendaItem::create(['meeting_id' => $meeting->id, 'order' => 1, 'title' => '開会宣言']);

        $response = $this->actingAs($general)->get(route('meetings.invitation.edit', $meeting));

        $response->assertOk();
        $response->assertSee('1. 開会宣言');
    }
}
