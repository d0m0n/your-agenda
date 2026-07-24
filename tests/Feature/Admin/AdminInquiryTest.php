<?php

namespace Tests\Feature\Admin;

use App\Enums\InquiryCategory;
use App\Enums\UserRole;
use App\Models\Inquiry;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInquiryTest extends TestCase
{
    use RefreshDatabase;

    private function makeSuperAdmin(): User
    {
        return User::factory()->create([
            'organization_id' => null,
            'role' => UserRole::SuperAdmin,
        ]);
    }

    public function test_general_and_observer_cannot_access_the_admin_inquiries_list(): void
    {
        $organization = Organization::factory()->create();
        $general = User::factory()->for($organization, 'organization')->create();
        $observer = User::factory()->for($organization, 'organization')->observer()->create();

        $this->actingAs($general)->get(route('admin.inquiries.index'))->assertForbidden();
        $this->actingAs($observer)->get(route('admin.inquiries.index'))->assertForbidden();
    }

    public function test_super_admin_sees_inquiries_from_all_organizations(): void
    {
        $admin = $this->makeSuperAdmin();

        $orgA = Organization::factory()->create(['name' => 'A組織']);
        $orgB = Organization::factory()->create(['name' => 'B組織']);

        Inquiry::factory()->for($orgA, 'organization')->create(['subject' => 'A組織の問い合わせ']);
        Inquiry::factory()->for($orgB, 'organization')->create(['subject' => 'B組織の問い合わせ']);

        $response = $this->actingAs($admin)->get(route('admin.inquiries.index'));

        $response->assertOk();
        $response->assertSee('A組織の問い合わせ');
        $response->assertSee('B組織の問い合わせ');
    }

    public function test_super_admin_can_filter_inquiries_by_status(): void
    {
        $admin = $this->makeSuperAdmin();
        $organization = Organization::factory()->create();

        Inquiry::factory()->for($organization, 'organization')->create(['subject' => '未対応の件']);
        Inquiry::factory()->for($organization, 'organization')->handled()->create(['subject' => '対応済みの件']);

        $unhandled = $this->actingAs($admin)->get(route('admin.inquiries.index', ['status' => 'unhandled']));
        $unhandled->assertSee('未対応の件');
        $unhandled->assertDontSee('対応済みの件');

        $handled = $this->actingAs($admin)->get(route('admin.inquiries.index', ['status' => 'handled']));
        $handled->assertSee('対応済みの件');
        $handled->assertDontSee('未対応の件');
    }

    public function test_super_admin_can_filter_inquiries_by_category(): void
    {
        $admin = $this->makeSuperAdmin();
        $organization = Organization::factory()->create();

        Inquiry::factory()->for($organization, 'organization')->create([
            'subject' => 'バグ報告の件',
            'category' => InquiryCategory::Bug,
        ]);
        Inquiry::factory()->for($organization, 'organization')->create([
            'subject' => '要望の件',
            'category' => InquiryCategory::FeatureRequest,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.inquiries.index', ['category' => InquiryCategory::Bug->value]));

        $response->assertSee('バグ報告の件');
        $response->assertDontSee('要望の件');
    }

    public function test_super_admin_can_search_inquiries_by_keyword(): void
    {
        $admin = $this->makeSuperAdmin();
        $organization = Organization::factory()->create(['name' => '検索対象組織']);

        Inquiry::factory()->for($organization, 'organization')->create(['subject' => '見つかるはず']);
        Inquiry::factory()->for(Organization::factory()->create(['name' => '別組織']), 'organization')->create(['subject' => '見つからないはず']);

        $response = $this->actingAs($admin)->get(route('admin.inquiries.index', ['q' => '検索対象組織']));

        $response->assertSee('見つかるはず');
        $response->assertDontSee('見つからないはず');
    }

    public function test_super_admin_can_filter_inquiries_by_organization(): void
    {
        $admin = $this->makeSuperAdmin();
        $orgA = Organization::factory()->create(['name' => 'A組織']);
        $orgB = Organization::factory()->create(['name' => 'B組織']);

        Inquiry::factory()->for($orgA, 'organization')->create(['subject' => 'A組織の件']);
        Inquiry::factory()->for($orgB, 'organization')->create(['subject' => 'B組織の件']);

        $response = $this->actingAs($admin)->get(route('admin.inquiries.index', ['organization_id' => $orgA->id]));

        $response->assertOk();
        $response->assertSee('A組織の件');
        $response->assertDontSee('B組織の件');
        $response->assertSee(__('組織で絞り込み中'));
    }

    public function test_super_admin_can_toggle_handled_status(): void
    {
        $admin = $this->makeSuperAdmin();
        $organization = Organization::factory()->create();
        $inquiry = Inquiry::factory()->for($organization, 'organization')->create();

        $this->assertFalse($inquiry->isHandled());

        $this->actingAs($admin)
            ->patch(route('admin.inquiries.toggle-handled', $inquiry))
            ->assertRedirect();

        $this->assertTrue($inquiry->fresh()->isHandled());

        $this->actingAs($admin)
            ->patch(route('admin.inquiries.toggle-handled', $inquiry))
            ->assertRedirect();

        $this->assertFalse($inquiry->fresh()->isHandled());
    }
}
