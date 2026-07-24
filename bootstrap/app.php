<?php

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
            'admin.basic_auth' => \App\Http\Middleware\AdminBasicAuth::class,
            'subscribed' => \App\Http\Middleware\EnsureOrganizationHasAccess::class,
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
            before: \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            prepend: \App\Http\Middleware\AdminBasicAuth::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
