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
}
