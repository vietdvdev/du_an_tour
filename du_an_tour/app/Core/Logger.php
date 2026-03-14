<?php
namespace App\Core;

class Logger
{
    public static function info(string $message, array $context = []): void
    {
        error_log('[INFO] ' . $message . ' ' . json_encode($context, JSON_UNESCAPED_UNICODE));
    }
    public static function error(string $message, array $context = []): void
    {
        error_log('[ERROR] ' . $message . ' ' . json_encode($context, JSON_UNESCAPED_UNICODE));
    }
}
