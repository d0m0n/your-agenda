<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * 本番で未知の500系エラーが発生した際に運営者へ送る通知メール。
 * App\Services\ErrorAlertMailerから送信される。スタックトレース全体は
 * 本文に含めない(メールという性質上、サーバーのログより保護が弱いため)。
 * 詳細調査はサーバー上のstorage/logs/laravel.logを確認する前提。
 */
class UnexpectedErrorOccurred extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $exceptionClass,
        public string $message,
        public string $file,
        public int $line,
        public ?string $url,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('[あなた(の)次第] エラーが発生しました: :class', ['class' => class_basename($this->exceptionClass)]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.unexpected-error',
        );
    }
}
