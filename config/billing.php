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

    // プラスプラン(スタンダードの上位プラン)の表示用月額。金額・機能とも
    // 未確定のため、Stripe側の価格連携はまだ行わず、管理者パネルからの
    // 手動切り替え(organizations.plan)のみで運用する。金額が決まり次第、
    // monthly_price_id/monthly_price_yenと同様の項目を追加して自己サービス化する想定。
    'plus_price_yen' => null,

    // トライアル終了・解約等でhasActiveAccess()がfalseになってから、
    // 再契約が無いまま組織を自動的に完全削除するまでの猶予日数。
    // organizations.access_lost_atからの経過日数で判定する
    // (ProcessOrganizationRetentionコマンド、毎日実行)。
    'deletion_grace_period_days' => env('DELETION_GRACE_PERIOD_DAYS', 90),
];
