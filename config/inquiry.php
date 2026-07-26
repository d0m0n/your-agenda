<?php

return [
    // 新規お問い合わせ受信時の通知先。専用のINQUIRY_NOTIFICATION_EMAILが
    // 未設定なら、エラー通知・バックアップ通知等と同じ運営者の連絡先メール
    // アドレスにフォールバックする(configの読み込み順に依存しないよう、
    // env()を直接ネストして参照している。config/error_alerts.phpと同じ方針)。
    'notify_email' => env(
        'INQUIRY_NOTIFICATION_EMAIL',
        env('ERROR_ALERT_EMAIL', env('BACKUP_NOTIFICATION_EMAIL', env('LEGAL_CONTACT_EMAIL')))
    ),
];
