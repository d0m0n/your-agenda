<?php

return [
    // 本番で未知の500系エラーが発生した際の通知先。専用のERROR_ALERT_EMAILが
    // 未設定なら、バックアップ通知・特商法表記等で使っている運営者の連絡先
    // メールアドレスを流用する。envの読み込み順に依存しないよう、config()
    // 経由ではなくenv()を直接ネストして参照している(config/backup.phpと同じ方針)。
    'mail_to' => env('ERROR_ALERT_EMAIL', env('BACKUP_NOTIFICATION_EMAIL', env('LEGAL_CONTACT_EMAIL'))),

    // 同一エラー(例外クラス+発生箇所)の通知を、この分数の間は1通に絞る
    // (同じバグが連続発生した場合に大量のメールが届くのを防ぐ)。
    'throttle_minutes' => env('ERROR_ALERT_THROTTLE_MINUTES', 60),
];
