<?php

return [
    // 管理者パネル(/admin)へのBasic認証。ユーザー名・パスワードの
    // どちらか一方でも未設定(null)の場合はBasic認証を要求しない
    // (ローカル開発では未設定のままにし、本番の.envでのみ設定する運用)。
    'basic_auth_username' => env('ADMIN_BASIC_AUTH_USERNAME'),
    'basic_auth_password' => env('ADMIN_BASIC_AUTH_PASSWORD'),

    // super_adminアカウントのログイン試行回数制限。しきい値に達すると
    // cooldown_minutes分だけアカウントをロックし、経過後は自動的に
    // 解除される(手動でのロック解除操作は不要)。
    'lockout_threshold' => 10,
    'lockout_cooldown_minutes' => 30,
];
