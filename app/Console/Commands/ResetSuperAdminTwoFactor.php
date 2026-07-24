<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * super_adminが認証アプリの入った端末を紛失し、かつリカバリーコードも
 * 使い果たした/紛失した場合の最終手段。二段階認証の設定を解除し、
 * 次回ログイン時に再度セットアップ(QR表示)からやり直せるようにする。
 * UIからの操作経路はなく、admin:create-super-adminと同様サーバー上での
 * artisan実行のみで行う運用(本人確認をSSHアクセス権限に委ねる)。
 */
class ResetSuperAdminTwoFactor extends Command
{
    protected $signature = 'admin:reset-two-factor {email}';

    protected $description = '管理者アカウント(super_admin)の二段階認証設定をリセットする';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user || ! $user->isSuperAdmin()) {
            $this->error('指定されたメールアドレスのsuper_adminアカウントが見つかりません。');

            return self::FAILURE;
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ])->save();

        $this->info("{$user->email} の二段階認証設定をリセットしました。次回ログイン時に再設定が必要です。");

        return self::SUCCESS;
    }
}
