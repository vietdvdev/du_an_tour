<?php

namespace App\Core;

class Session
{
    /** Bắt đầu session nếu chưa bắt đầu */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /** Lưu dữ liệu vào session */
    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /** Lấy dữ liệu từ session */
    public static function get(string $key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /** Xóa session key */
    public static function forget(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /** Lấy toàn bộ session */
    public static function all(): array
    {
        self::start();
        return $_SESSION;
    }

    /** Flash message (Tự xóa sau khi đọc) */
    public static function flash(string $key, $value = null)
    {
        self::start();

        // SET flash
        if ($value !== null) {
            $_SESSION['flash'][$key] = $value;
            return;
        }

        // GET flash
        $data = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $data;
    }

    /** Lưu old input */
    public static function old(string $key, $default = '')
    {
        self::start();
        return $_SESSION['old'][$key] ?? $default;
    }

    /** Lưu tất cả input vào old */
    public static function setOld(array $data): void
    {
        self::start();
        $_SESSION['old'] = $data;
    }

    /** Tạo CSRF token */
    public static function token(): string
    {
        self::start();

        if (!isset($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_token'];
    }
}
