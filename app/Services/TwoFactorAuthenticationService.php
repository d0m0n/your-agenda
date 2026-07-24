<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * super_adminアカウント向けTOTP二段階認証(認証アプリ方式)のセットアップ・
 * 検証・リカバリーコード管理を担当する。
 */
class TwoFactorAuthenticationService
{
    private const RECOVERY_CODE_COUNT = 8;

    public function __construct(private readonly Google2FA $engine)
    {
    }

    public function generateSecretKey(): string
    {
        return $this->engine->generateSecretKey();
    }

    public function qrCodeUrl(User $user, string $secret): string
    {
        return $this->engine->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret,
        );
    }

    public function verify(string $secret, string $code): bool
    {
        return $this->engine->verifyKey($secret, $code);
    }

    /**
     * @return array<int, string>
     */
    public function generateRecoveryCodes(): array
    {
        return collect(range(1, self::RECOVERY_CODE_COUNT))
            ->map(fn () => Str::upper(Str::random(4).'-'.Str::random(4)))
            ->all();
    }

    /**
     * リカバリーコードを1つ消費する。一致すれば残りのコード配列を返し、
     * 一致しなければnullを返す(呼び出し側で判定する)。
     *
     * @param  array<int, string>  $recoveryCodes
     * @return array<int, string>|null
     */
    public function consumeRecoveryCode(array $recoveryCodes, string $submitted): ?array
    {
        $submitted = Str::upper(trim($submitted));

        if (! in_array($submitted, $recoveryCodes, true)) {
            return null;
        }

        return array_values(array_diff($recoveryCodes, [$submitted]));
    }
}
