<?php

namespace App\Enums;

enum InquiryCategory: string
{
    case Inquiry = 'inquiry';
    case Bug = 'bug';
    case FeatureRequest = 'feature_request';

    public function label(): string
    {
        return match ($this) {
            self::Inquiry => '問い合わせ',
            self::Bug => '不具合報告',
            self::FeatureRequest => '機能追加の要望',
        };
    }
}
