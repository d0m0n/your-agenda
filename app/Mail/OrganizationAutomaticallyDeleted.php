<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

/**
 * 猶予期間(config('billing.deletion_grace_period_days'))経過により
 * 組織が自動削除された際、運営者への記録・確認用に送る通知メール。
 * ProcessOrganizationRetentionコマンドから送信される。
 */
class OrganizationAutomaticallyDeleted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $organizationName,
        public int $organizationId,
        public Carbon $accessLostAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('[あなた(の)次第] 組織を自動削除しました: :name', ['name' => $this->organizationName]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.organization-automatically-deleted',
        );
    }
}
