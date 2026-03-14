<?php
namespace App\Core;

class ErrorHandler
{
    public static function register(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }

    public static function handleError($severity, $message, $file, $line): void
    {
        if (!(error_reporting() & $severity)) return;
        self::render('PHP Error: ' . $message, $file, $line);
    }

    public static function handleException($e): void
    {
        self::render('Uncaught Exception: ' . $e->getMessage(), $e->getFile(), $e->getLine());
    }

    protected static function render(string $title, string $file, int $line): void
    {
        http_response_code(500);
        if (filter_var(getenv('APP_DEBUG') ?: 'true', FILTER_VALIDATE_BOOLEAN)) {
            echo '<h1>' . htmlspecialchars($title) . '</h1>';
            echo '<p>in ' . htmlspecialchars($file) . ' on line ' . $line . '</p>';
        } else {
            echo '<h1>Server Error</h1>';
        }
        exit;
    }
}
