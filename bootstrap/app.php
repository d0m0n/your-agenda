<?php

use App\Http\Middleware\AdminBasicAuth;
use App\Http\Middleware\EnsureOrganizationHasAccess;
use App\Services\ErrorAlertMailer;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.basic_auth' => AdminBasicAuth::class,
            'subscribed' => EnsureOrganizationHasAccess::class,
        ]);

        // StripeからのWebhookはCookie/セッションを持たないためCSRFトークンを
        // 送れない。Cashier標準のWebhookControllerが署名(STRIPE_WEBHOOK_SECRET)
        // を検証するため、CSRF検証は不要かつ邪魔になる。
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);

        // セッション認証(auth)より先にBasic認証を実行させる。
        // 未設定なら通過するだけなので、他のリクエストへの影響はない。
        // (優先度リストにはAuthenticateの実クラスではなく、Laravel既定の
        // 優先度配列に載っているAuthenticatesRequestsコントラクトを基準にする
        // 必要がある。実クラスを指定すると一致せず、優先度リストの末尾=
        // 最低優先度に追加されてしまい、authより後に実行されてしまう)
        $middleware->prependToPriorityList(
            before: AuthenticatesRequests::class,
            prepend: AdminBasicAuth::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // 本番で未知の500系エラーが発生した際、運営者へメールで知らせる
        // (ErrorAlertMailer参照)。falseを返さないため、通常のログ出力
        // (storage/logs/laravel.log)は今まで通り行われる。
        $exceptions->report(function (Throwable $e): void {
            app(ErrorAlertMailer::class)->notifyIfNeeded($e);
        });
    })->create();
