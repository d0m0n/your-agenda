<?php

namespace Tests\Feature;

use App\Enums\InquiryCategory;
use App\Mail\NewInquiryReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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

    public function test_submitting_an_inquiry_notifies_the_configured_recipient(): void
    {
        Mail::fake();
        config(['inquiry.notify_email' => 'admin@example.com']);

        [, $general] = $this->createTenant();

        $this->actingAs($general)->post(route('inquiries.store'), [
            'category' => InquiryCategory::Inquiry->value,
            'subject' => '料金について',
            'body' => 'プラン変更について教えてください。',
        ]);

        Mail::assertSent(NewInquiryReceived::class, function ($mail) {
            return $mail->hasTo('admin@example.com')
                && $mail->inquiry->subject === '料金について';
        });
    }

    public function test_submitting_an_inquiry_does_not_notify_when_no_recipient_is_configured(): void
    {
        Mail::fake();
        config(['inquiry.notify_email' => null]);

        [, $general] = $this->createTenant();

        $this->actingAs($general)->post(route('inquiries.store'), [
            'category' => InquiryCategory::Inquiry->value,
            'subject' => '料金について',
            'body' => 'プラン変更について教えてください。',
        ]);

        Mail::assertNothingSent();
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
