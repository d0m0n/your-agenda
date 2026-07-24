<?php

namespace Tests\Feature;

use App\Enums\InquiryCategory;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class InquiryTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_general_user_can_submit_an_inquiry(): void
    {
        [$organization, $general] = $this->createTenant();

        $response = $this->actingAs($general)->post(route('inquiries.store'), [
            'category' => InquiryCategory::Bug->value,
            'subject' => 'ログインできません',
            'body' => 'パスワードを入力してもエラーになります。',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('inquiries', [
            'organization_id' => $organization->id,
            'user_id' => $general->id,
            'category' => InquiryCategory::Bug->value,
            'subject' => 'ログインできません',
        ]);
    }

    public function test_observer_can_also_submit_an_inquiry(): void
    {
        [$organization, , $observer] = $this->createTenant();

        $response = $this->actingAs($observer)->post(route('inquiries.store'), [
            'category' => InquiryCategory::FeatureRequest->value,
            'subject' => 'カレンダー同期がほしい',
            'body' => '他の予定管理ツールとの同期機能が欲しいです。',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('inquiries', [
            'organization_id' => $organization->id,
            'user_id' => $observer->id,
            'category' => InquiryCategory::FeatureRequest->value,
        ]);
    }

    public function test_inquiry_requires_category_subject_and_body(): void
    {
        [, $general] = $this->createTenant();

        $response = $this->actingAs($general)->post(route('inquiries.store'), []);

        $response->assertSessionHasErrors(['category', 'subject', 'body']);
        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_inquiry_icon_opens_form_for_general_and_observer(): void
    {
        [, $general, $observer] = $this->createTenant();

        $this->actingAs($general)->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('お問い合わせ'));

        $this->actingAs($observer)->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('お問い合わせ'));
    }
}
