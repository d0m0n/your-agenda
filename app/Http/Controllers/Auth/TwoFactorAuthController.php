<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\QrCodeService;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * super_adminアカウント専用の二段階認証(TOTP)フロー。
 * パスワード認証成功後、LoginRequestがAuth::loginを呼ばずに
 * session('two_factor.login.id')へ保留中のユーザーIDを詰めるので、
 * ここで初回セットアップ(QR表示・コード確認)または毎回のログイン確認
 * (コード入力 or リカバリーコード)を行ってから、実際にログインさせる。
 */
class TwoFactorAuthController extends Controller
{
    public function __construct(
        private readonly TwoFactorAuthenticationService $twoFactor,
        private readonly QrCodeService $qrCode,
    ) {
    }

    public function show(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasConfirmedTwoFactorAuthentication()) {
            return view('auth.two-factor-challenge');
        }

        if (! $user->two_factor_secret) {
            $user->forceFill(['two_factor_secret' => $this->twoFactor->generateSecretKey()])->save();
        }

        $qrCodeUrl = $this->twoFactor->qrCodeUrl($user, $user->two_factor_secret);

        return view('auth.two-factor-setup', [
            'secret' => $user->two_factor_secret,
            'qrCodeImage' => $this->qrCode->dataUri($qrCodeUrl),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        return $user->hasConfirmedTwoFactorAuthentication()
            ? $this->handleChallenge($request, $user)
            : $this->handleSetup($request, $user);
    }

    public function recoveryCodes(Request $request): View|RedirectResponse
    {
        $codes = $request->session()->pull('two_factor.recovery_codes');

        if (! $codes) {
            return redirect()->route('dashboard');
        }

        return view('auth.two-factor-recovery-codes', ['codes' => $codes]);
    }

    private function handleSetup(Request $request, User $user): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        if (! $this->twoFactor->verify($user->two_factor_secret, $request->string('code'))) {
            throw ValidationException::withMessages([
                'code' => __('確認コードが正しくありません。認証アプリに表示されている6桁のコードを入力してください。'),
            ]);
        }

        $recoveryCodes = $this->twoFactor->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $recoveryCodes,
        ])->save();

        $this->login($request, $user);

        $request->session()->put('two_factor.recovery_codes', $recoveryCodes);

        return redirect()->route('two-factor.recovery-codes');
    }

    private function handleChallenge(Request $request, User $user): RedirectResponse
    {
        if ($user->isLocked()) {
            throw ValidationException::withMessages([
                'code' => __('ログイン試行回数の上限に達したため、アカウントが一時的にロックされています。しばらくしてから再度お試しください。'),
            ]);
        }

        $request->validate([
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $verified = false;

        if ($request->filled('code') && $this->twoFactor->verify($user->two_factor_secret, $request->string('code'))) {
            $verified = true;
        } elseif ($request->filled('recovery_code')) {
            $remaining = $this->twoFactor->consumeRecoveryCode(
                $user->two_factor_recovery_codes ?? [],
                $request->string('recovery_code'),
            );

            if ($remaining !== null) {
                $user->forceFill(['two_factor_recovery_codes' => $remaining])->save();
                $verified = true;
            }
        }

        if (! $verified) {
            $user->registerFailedLoginAttempt();

            throw ValidationException::withMessages([
                'code' => $user->isLocked()
                    ? __('ログイン試行回数の上限に達したため、アカウントが一時的にロックされています。しばらくしてから再度お試しください。')
                    : __('コードが正しくありません。'),
            ]);
        }

        $user->clearFailedLoginAttempts();

        $this->login($request, $user);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function login(Request $request, User $user): void
    {
        $remember = (bool) $request->session()->pull('two_factor.login.remember', false);
        $request->session()->forget('two_factor.login.id');

        Auth::login($user, $remember);

        $request->session()->regenerate();
    }

    private function pendingUser(Request $request): ?User
    {
        $id = $request->session()->get('two_factor.login.id');

        return $id ? User::find($id) : null;
    }
}
