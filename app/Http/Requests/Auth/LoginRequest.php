<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * super_adminアカウントについては、パスワード認証成功後もこの時点では
     * ログインさせず(Auth::loginを呼ばない)、二段階認証(TOTP)の確認・
     * セットアップを挟む。保留中のユーザーIDはセッションに一時保存し、
     * AuthenticatedSessionControllerがそれを見て遷移先を振り分ける。
     * また、super_adminはログイン失敗10回でアカウントを一定時間ロックする
     * (config('admin_security.lockout_*'))。一般/オブザーブユーザーは
     * 対象外で、従来通りAuth::attemptで即ログインする。
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('email', $this->string('email'))->first();

        if ($user?->isSuperAdmin() && $user->isLocked()) {
            throw ValidationException::withMessages([
                'email' => __('ログイン試行回数の上限に達したため、アカウントが一時的にロックされています。しばらくしてから再度お試しください。'),
            ]);
        }

        if (! $user || ! Hash::check((string) $this->string('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey());

            if ($user?->isSuperAdmin()) {
                $user->registerFailedLoginAttempt();
            }

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        if ($user->isSuperAdmin()) {
            $user->clearFailedLoginAttempts();

            $this->session()->put('two_factor.login.id', $user->id);
            $this->session()->put('two_factor.login.remember', $this->boolean('remember'));

            return;
        }

        Auth::login($user, $this->boolean('remember'));
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
