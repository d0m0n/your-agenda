<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * 新規お問い合わせ受信時に運営者へ知らせる通知メール。
 * InquiryController::store()から送信される。管理者パネルを定期的に
 * 見に行かなくても、新着に気づけるようにするためのもの。
 */
class NewInquiryReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Inquiry $inquiry)
    {
        $this->inquiry->loadMissing(['user', 'organization']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('[あなた(の)次第] 新しいお問い合わせ: :category', [
                'category' => $this->inquiry->category->label(),
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-inquiry-received',
            with: [
                'adminUrl' => route('admin.inquiries.index'),
            ],
        );
    }
}
