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
];
