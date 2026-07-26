<?php

namespace App\Services;

use App\Mail\UnexpectedErrorOccurred;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * 本番で未知の500系エラーが発生した際、運営者へメールで知らせる
 * (Sentry等の外部サービスは使わず、既存のMail基盤で完結させるひとり運用向けの仕組み)。
 * バリデーション・404・認証エラー等、ユーザー操作で日常的に起こり得る
 * 「想定内」の例外は通知しない。bootstrap/app.phpのwithExceptions()から呼ばれる。
 */
class ErrorAlertMailer
{
    /**
     * @var array<class-string<Throwable>>
     */
    private const array EXPECTED_EXCEPTIONS = [
        ValidationException::class,
        AuthenticationException::class,
        AuthorizationException::class,
        ModelNotFoundException::class,
        TokenMismatchException::class,
    ];

    public function notifyIfNeeded(Throwable $e): void
    {
        if (! app()->environment('production')) {
            return;
        }

        if (! config('error_alerts.mail_to')) {
            return;
        }

        if ($this->isExpected($e) || $this->recentlyNotified($e)) {
            return;
        }

        Mail::to(config('error_alerts.mail_to'))->send(new UnexpectedErrorOccurred(
            exceptionClass: $e::class,
            message: $e->getMessage() ?: '(メッセージなし)',
            file: $e->getFile(),
            line: $e->getLine(),
            url: request()?->fullUrl(),
        ));
    }

    private function isExpected(Throwable $e): bool
    {
        foreach (self::EXPECTED_EXCEPTIONS as $class) {
            if ($e instanceof $class) {
                return true;
            }
        }

        // 4xx系(404, 419, 429等)はアプリのバグではなく通常のユーザー操作で
        // 起こり得るため通知しない。5xx系(500等)だけを通知対象にする。
        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode() < 500;
        }

        return false;
    }

    private function recentlyNotified(Throwable $e): bool
    {
        $key = 'error-alert:'.md5($e::class.$e->getFile().$e->getLine());

        if (Cache::has($key)) {
            return true;
        }

        Cache::put($key, true, now()->addMinutes(config('error_alerts.throttle_minutes', 60)));

        return false;
    }
}
