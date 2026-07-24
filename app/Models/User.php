<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['organization_id', 'role', 'name', 'email', 'password', 'storage_quota_bytes'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
            'locked_until' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the sites uploaded by this user.
     *
     * @return HasMany<Site, $this>
     */
    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function isGeneral(): bool
    {
        return $this->role === UserRole::General;
    }

    public function isObserver(): bool
    {
        return $this->role === UserRole::Observer;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function storageQuotaBytes(): int
    {
        return $this->storage_quota_bytes ?? config('storage_quota.default_bytes');
    }

    public function hasConfirmedTwoFactorAuthentication(): bool
    {
        return ! is_null($this->two_factor_confirmed_at);
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    /**
     * ログイン失敗を1回記録する。super_adminのアカウントロック機能
     * (config('admin_security.lockout_threshold')回で一定時間ロック)
     * のためのカウンター。呼び出し側でisSuperAdmin()を確認してから使うこと。
     */
    public function registerFailedLoginAttempt(): void
    {
        $attempts = $this->failed_login_attempts + 1;
        $threshold = config('admin_security.lockout_threshold');

        $this->forceFill([
            'failed_login_attempts' => $attempts,
            'locked_until' => $attempts >= $threshold
                ? now()->addMinutes(config('admin_security.lockout_cooldown_minutes'))
                : $this->locked_until,
        ])->save();
    }

    public function clearFailedLoginAttempts(): void
    {
        $this->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ])->save();
    }
}
