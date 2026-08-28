<?php

return [
    // 組織登録から無料で利用できる日数。カード情報の入力は求めず、
    // この日数を過ぎてから保護された画面にアクセスした時点でペイウォールを表示する
    // (RegisteredUserController、EnsureOrganizationHasAccessミドルウェア参照)。
    'trial_days' => 14,

    // Stripeダッシュボード(テストモード/本番モード)で作成した、月額プランの
    // Price ID。JPYは0桁通貨のため、Stripe側のunit_amountは600(60000ではない)
    // で作成すること。
    'monthly_price_id' => env('STRIPE_PRICE_ID_MONTHLY'),

    // LP・ペイウォール画面での表示用(Stripe側の実際の価格設定とは独立した表示専用の値)。
    'monthly_price_yen' => 600,

    // Stripeダッシュボードで作成した、プラスプラン用Price ID。スタンダードと
    // 同じ「組織=1サブスクリプション」のまま、価格だけを月額1,200円のPriceに
    // 差し替える方式(アドオン courses ではない)。基本設定画面からのセルフ
    // サービス切替(BillingController::updatePlan())と、プラス選択中にトライアルが
    // 終了した場合のCheckout(BillingController::checkout())の両方で使う。
    'plus_price_id' => env('STRIPE_PRICE_ID_PLUS'),

    // LP・基本設定・ペイウォール画面での表示用月額(税込)。
    'plus_price_yen' => 1200,

    // トライアル終了・解約等でhasActiveAccess()がfalseになってから、
    // 再契約が無いまま組織を自動的に完全削除するまでの猶予日数。
    // organizations.access_lost_atからの経過日数で判定する
    // (ProcessOrganizationRetentionコマンド、毎日実行)。
    'deletion_grace_period_days' => (int) env('DELETION_GRACE_PERIOD_DAYS', 90),
];
