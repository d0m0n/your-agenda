<?php

namespace App\Mail;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

/**
 * トライアル終了・解約等でアクセス権を失った組織へ、猶予期間の開始と
 * 削除予定日を知らせるメール。ProcessOrganizationRetentionコマンドから、
 * access_lost_atを記録したタイミングで一般ユーザー全員に送る。
 */
class AccountScheduledForDeletion extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Organization $organization, public Carbon $deletionDate) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __(':name のデータは:dateに削除される予定です', [
                'name' => $this->organization->name,
                'date' => $this->deletionDate->format('Y年n月j日'),
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.account-scheduled-for-deletion',
            with: [
                'organization' => $this->organization,
                'deletionDate' => $this->deletionDate,
                'billingUrl' => route('billing.paywall'),
            ],
        );
    }
}
