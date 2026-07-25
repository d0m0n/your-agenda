<?php

namespace App\Mail;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * 無料トライアル終了が近いことを知らせるリマインドメール。
 * SendTrialEndingRemindersコマンド(毎日実行)から、対象組織の一般ユーザー
 * 全員に送る。本番でキューワーカーの常駐を前提にできない
 * (さくらのレンタルサーバーの制約)ため、queue化はせず同期送信する。
 */
class TrialEndingSoonMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Organization $organization) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __(':name の無料お試し期間があと :days 日で終了します', [
                'name' => $this->organization->name,
                'days' => $this->organization->trialDaysRemaining(),
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.trial-ending-soon',
            with: [
                'organization' => $this->organization,
                'daysRemaining' => $this->organization->trialDaysRemaining(),
                'billingUrl' => route('billing.paywall'),
            ],
        );
    }
}
