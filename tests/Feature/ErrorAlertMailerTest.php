<?php

namespace Tests\Feature;

use App\Mail\UnexpectedErrorOccurred;
use App\Services\ErrorAlertMailer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

/**
 * 本番で未知の500系エラーが発生した際に運営者へメール通知する仕組み
 * (bootstrap/app.phpのwithExceptions()経由でApp\Services\ErrorAlertMailerが呼ばれる)。
 */
class ErrorAlertMailerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config(['error_alerts.mail_to' => 'admin@example.com']);
    }

    public function test_notifies_for_an_unexpected_exception_in_production(): void
    {
        Mail::fake();
        app()->detectEnvironment(fn () => 'production');

        app(ErrorAlertMailer::class)->notifyIfNeeded(new RuntimeException('something broke'));

        Mail::assertSent(UnexpectedErrorOccurred::class, fn ($mail) => $mail->message === 'something broke');
    }

    public function test_does_not_notify_outside_production(): void
    {
        Mail::fake();
        app()->detectEnvironment(fn () => 'local');

        app(ErrorAlertMailer::class)->notifyIfNeeded(new RuntimeException('something broke'));

        Mail::assertNothingSent();
    }

    public function test_does_not_notify_when_no_recipient_is_configured(): void
    {
        Mail::fake();
        config(['error_alerts.mail_to' => null]);
        app()->detectEnvironment(fn () => 'production');

        app(ErrorAlertMailer::class)->notifyIfNeeded(new RuntimeException('something broke'));

        Mail::assertNothingSent();
    }

    public function test_does_not_notify_for_validation_exceptions(): void
    {
        Mail::fake();
        app()->detectEnvironment(fn () => 'production');

        app(ErrorAlertMailer::class)->notifyIfNeeded(
            ValidationException::withMessages(['email' => 'required'])
        );

        Mail::assertNothingSent();
    }

    public function test_does_not_notify_for_404_http_exceptions(): void
    {
        Mail::fake();
        app()->detectEnvironment(fn () => 'production');

        app(ErrorAlertMailer::class)->notifyIfNeeded(new NotFoundHttpException('not found'));

        Mail::assertNothingSent();
    }

    public function test_throttles_repeated_notifications_for_the_same_error(): void
    {
        Mail::fake();
        app()->detectEnvironment(fn () => 'production');

        $mailer = app(ErrorAlertMailer::class);
        $e = new RuntimeException('repeated error');

        $mailer->notifyIfNeeded($e);
        $mailer->notifyIfNeeded($e);
        $mailer->notifyIfNeeded($e);

        Mail::assertSent(UnexpectedErrorOccurred::class, 1);
    }
}
