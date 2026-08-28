<?php

namespace App\Enums;

enum OrganizationPlan: string
{
    case Standard = 'standard';
    case Plus = 'plus';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'スタンダード',
            self::Plus => 'プラス',
        };
    }

    /**
     * このプランに対応するStripeのPrice ID。組織のサブスクリプションは
     * 常にこのPrice1本のみを持つ(アドオンではなく価格そのものの差し替え)。
     */
    public function priceId(): ?string
    {
        return match ($this) {
            self::Standard => config('billing.monthly_price_id'),
            self::Plus => config('billing.plus_price_id'),
        };
    }

    /**
     * 表示用の月額(税込)。
     */
    public function priceYen(): int
    {
        return match ($this) {
            self::Standard => (int) config('billing.monthly_price_yen'),
            self::Plus => (int) config('billing.plus_price_yen'),
        };
    }
}
