<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    /**
     * トライアル終了後(または未契約状態)に案内するペイウォール画面。
     * 一般ユーザーには支払いボタンを、オブザーブユーザーには一般ユーザーへの
     * 依頼を案内する(支払い操作は一般ユーザーのみ、checkout()参照)。
     */
    public function show(Request $request): View
    {
        return view('billing.paywall', [
            'organization' => $request->user()->organization,
        ]);
    }

    /**
     * Stripe Checkoutのホスト型決済ページへリダイレクトする。カード情報は
     * 当社サーバーを一切経由しない。支払い完了後はStripeのWebhookで
     * サブスクリプションが作成され、hasActiveAccess()がtrueに戻る。
     */
    public function checkout(Request $request)
    {
        $organization = $request->user()->organization;

        return $organization->newSubscription('default', config('billing.monthly_price_id'))
            ->checkout([
                'success_url' => route('billing.success'),
                'cancel_url' => route('billing.paywall'),
            ]);
    }

    public function success(Request $request): RedirectResponse
    {
        return redirect()->route('dashboard')
            ->with('status', __('お支払い手続きが完了しました。ご利用ありがとうございます。'));
    }
}
