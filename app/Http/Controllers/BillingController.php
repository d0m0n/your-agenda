<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Checkout;
use Throwable;

class BillingController extends Controller
{
    /**
     * トライアル終了後(または未契約状態)に案内するペイウォール画面。
     * 一般ユーザーには支払いボタンを、オブザーブユーザーには一般ユーザーへの
     * 依頼を案内する(支払い操作は一般ユーザーのみ、checkout()参照)。
     * すでに有効なサブスクリプションを持つ組織がブックマーク等でここへ
     * 直接アクセスした場合は、二重契約を避けるため基本設定へ流す
     * (checkout()側にも同じガードがある)。
     */
    public function show(Request $request): View|RedirectResponse
    {
        $organization = $request->user()->organization;

        if ($organization->subscribed('default')) {
            return redirect()->route('settings.edit');
        }

        return view('billing.paywall', [
            'organization' => $organization,
        ]);
    }

    /**
     * Stripe Checkoutのホスト型決済ページへリダイレクトする。カード情報は
     * 当社サーバーを一切経由しない。支払い完了後はStripeのWebhookで
     * サブスクリプションが作成され、hasActiveAccess()がtrueに戻る。
     *
     * Stripe Checkoutは「このお客様はすでに契約中か」を自動では見ないため、
     * 何もガードせずに呼ぶと、支払いボタンの連打やペイウォールへの再訪問で
     * 同じ組織に並行したサブスクリプションが複数作られ、二重・三重課金に
     * つながる。そのため、既に有効な契約がある場合はここで弾く。
     */
    public function checkout(Request $request): RedirectResponse|Checkout
    {
        $organization = $request->user()->organization;

        if ($organization->subscribed('default')) {
            return redirect()->route('settings.edit')
                ->with('status', __('すでにお支払い情報が登録されています。'));
        }

        return $organization->newSubscription('default', config('billing.monthly_price_id'))
            ->checkout([
                // {CHECKOUT_SESSION_ID}はStripeが実際のセッションIDに置き換える
                // プレースホルダー文字列(Stripe側の仕様のため、URLエンコードせず
                // そのまま連結すること)。success()がこれを使ってWebhookの到着を
                // 待たずに即時同期する。
                'success_url' => route('billing.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('billing.paywall'),
            ]);
    }

    /**
     * Stripe Checkout完了後の戻り先。Webhookでのサブスクリプション反映は
     * 非同期(数秒遅れる、あるいはローカル開発でstripe listenを起動し忘れて
     * いると届かないことすらある)。ここで即座に/dashboardへ飛ばすと
     * subscribedミドルウェアにまだ弾かれてペイウォールへ逆戻りしてしまう
     * ことがあるため、session_idがあればWebhookを待たずにChecked
     * Session APIから即時同期を試み(syncSubscriptionFromCheckoutSession)、
     * それでも反映が間に合わなければ中間画面でstatus()をポーリングして
     * 有効化を確認してからリダイレクトする。
     *
     * 行き先は、ペイウォールに弾かれる直前にいた画面(url.intended、
     * EnsureOrganizationHasAccessが設定)があればそこへ、無ければ
     * ダッシュボードへ戻す。
     */
    public function success(Request $request): View
    {
        $organization = $request->user()->organization;

        if ($organization && $request->filled('session_id')) {
            $this->syncSubscriptionFromCheckoutSession($organization, $request->string('session_id')->toString());
        }

        return view('billing.processing', [
            'redirectUrl' => $request->session()->pull('url.intended', route('dashboard')),
        ]);
    }

    /**
     * billing.processingがポーリングする、契約状態の確認用エンドポイント。
     */
    public function status(Request $request): JsonResponse
    {
        return response()->json([
            'active' => (bool) $request->user()->organization?->fresh()->hasActiveAccess(),
        ]);
    }

    /**
     * Stripeのカスタマーポータル(お支払い方法の変更・請求書の確認・解約)へ
     * リダイレクトする。当社サーバーはカード情報を一切扱わない。
     * Stripe側に顧客レコードが無い(一度も決済に進んだことが無い)組織は
     * 対象外(設定画面側でボタンを出し分ける)。
     */
    public function portal(Request $request): RedirectResponse
    {
        return $request->user()->organization->redirectToBillingPortal(route('settings.edit'));
    }

    /**
     * Stripe CheckoutのセッションIDから、Webhookの到着を待たずに
     * サブスクリプションをこの場で同期する
     * (WebhookController::handleCustomerSubscriptionCreated()相当の処理を、
     * Webhookペイロードの代わりにChecked Session APIから行う)。
     *
     * session_idはクエリ文字列で利用者が改変できる値のため、取得した
     * セッションの顧客IDがこの組織のstripe_idと一致する場合のみ同期する
     * (他組織のセッションIDを渡されても反映されないようにするための
     * ガード)。失敗しても致命的ではない(この後のポーリングや、
     * 後から届く本来のWebhookが最終的に反映する)ため、例外は握りつぶして
     * ログにだけ残す。
     */
    private function syncSubscriptionFromCheckoutSession(Organization $organization, string $sessionId): void
    {
        try {
            $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId, [
                'expand' => ['subscription'],
            ]);

            $subscription = $session->subscription;

            if (! $subscription || $session->customer !== $organization->stripe_id) {
                return;
            }

            $firstItem = $subscription->items->first();

            $organization->subscriptions()->updateOrCreate(
                ['stripe_id' => $subscription->id],
                [
                    'type' => 'default',
                    'stripe_status' => $subscription->status,
                    'stripe_price' => $firstItem?->price?->id,
                    'quantity' => $firstItem?->quantity,
                    'trial_ends_at' => $subscription->trial_end ? Carbon::createFromTimestamp($subscription->trial_end) : null,
                    'ends_at' => null,
                ]
            );

            $organization->forceFill(['trial_ends_at' => null])->save();
        } catch (Throwable $e) {
            report($e);
        }
    }
}
