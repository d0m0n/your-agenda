<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * getMessage()に日本語のユーザー向け案内文をそのまま入れる
 * (App\Services\ClaudeMinutesGeneratorがAnthropic SDKの例外から変換して投げる)。
 */
class MinutesGenerationException extends RuntimeException
{
    //
}
