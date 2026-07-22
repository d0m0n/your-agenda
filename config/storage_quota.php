<?php

return [
    // 一般ユーザー1人あたりのデフォルト割り当て容量。管理者アカウントが
    // users.storage_quota_bytes を設定するとユーザーごとに上書きされる。
    'default_bytes' => 2 * 1024 * 1024 * 1024, // 2GB
];
