<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;

#[Fillable([
    'name', 'header_image_path', 'icon_image_path', 'google_calendar_id', 'contracted_at',
    'show_meetings_pane', 'show_calendar_pane', 'show_birthday_pane', 'show_materials_pane',
    'invitation_pdf_template', 'invitation_email_template', 'invitation_line_template',
    'free_access_enabled',
])]
class Organization extends Model
{
    use Billable, HasFactory;

    protected function casts(): array
    {
        return [
            'contracted_at' => 'date',
            'trial_ends_at' => 'datetime',
            'show_meetings_pane' => 'boolean',
            'show_calendar_pane' => 'boolean',
            'show_birthday_pane' => 'boolean',
            'show_materials_pane' => 'boolean',
            'free_access_enabled' => 'boolean',
        ];
    }

    /**
     * カード登録なしの14日間トライアル中(Cashierの汎用トライアル判定)か、
     * 有効なサブスクリプションを持っているか、または管理者が無償提供モードを
     * 有効にしているかどうか。EnsureOrganizationHasAccessミドルウェアが
     * これを見て保護ルートへのアクセス可否を判定する。
     */
    public function hasActiveAccess(): bool
    {
        return $this->free_access_enabled || $this->onGenericTrial() || $this->subscribed('default');
    }

    /**
     * トライアル中の残り日数(トライアル中でなければ0)。ナビのバッジ表示用。
     */
    public function trialDaysRemaining(): int
    {
        if (! $this->onGenericTrial()) {
            return 0;
        }

        return max(0, (int) now()->startOfDay()->diffInDays($this->trial_ends_at->startOfDay(), false));
    }

    /**
     * 管理者パネル表示用の契約状態ラベル。plan_status(廃止済み)の代わりに
     * Cashierのサブスクリプション状態・トライアル状態から都度導出する。
     */
    public function subscriptionStatusLabel(): string
    {
        if ($this->free_access_enabled) {
            return __('無償提供中');
        }

        if ($this->subscribed('default')) {
            return __('契約中');
        }

        if ($this->onGenericTrial()) {
            return __('トライアル中(残り:day日)', ['day' => $this->trialDaysRemaining()]);
        }

        if ($this->trial_ends_at) {
            return __('未契約(トライアル終了)');
        }

        return __('未契約');
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasMany<Site, $this>
     */
    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    /**
     * @return HasMany<Member, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    /**
     * @return HasMany<Meeting, $this>
     */
    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    /**
     * @return HasMany<Position, $this>
     */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    /**
     * @return HasMany<Inquiry, $this>
     */
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }
}
