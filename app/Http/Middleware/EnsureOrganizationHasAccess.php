<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 14日間の無料トライアルが終了し、かつ有効なサブスクリプションも無い組織の
 * ユーザーを、通常の管理画面から締め出してペイウォール(billing.paywall)へ
 * 誘導する。super_adminは組織を持たないため対象外(このミドルウェアは
 * routes/web.phpの一般/オブザーブユーザー向けルートにのみ適用する)。
 */
class EnsureOrganizationHasAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        if ($user->organization?->hasActiveAccess()) {
            return $next($request);
        }

        return redirect()->route('billing.paywall');
    }
}
