<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

/**
 * PDF案内文に地図等へのリンクをQRコードとして埋め込むための生成器。
 * 外部APIには依存せず、サーバー内で完結して生成する。
 */
class QrCodeService
{
    public function dataUri(string $data, int $size = 220): string
    {
        $result = (new Builder(
            writer: new PngWriter(),
            data: $data,
            size: $size,
            margin: 8,
        ))->build();

        return $result->getDataUri();
    }
}
