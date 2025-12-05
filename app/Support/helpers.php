<?php

use App\Core\Session;
use App\Core\Response;

// ------------------------------
// dump & die
// ------------------------------
if (!function_exists('dd')) {
    function dd(...$vars) {
        echo '<pre>';
        foreach ($vars as $v) var_dump($v);
        echo '</pre>';
        die();
    }
}

// ------------------------------
// đọc biến môi trường
// ------------------------------
if (!function_exists('envv')) {
    function envv(string $key, $default = null) {
        $v = getenv($key);
        return $v === false ? $default : $v;
    }
}

// ------------------------------
// random string
// ------------------------------
if (!function_exists('str_random')) {
    function str_random(int $len = 16): string {
        return bin2hex(random_bytes(max(1, (int)($len / 2))));
    }
}

// ------------------------------
// CSRF helper
// ------------------------------
if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        return Session::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars(Session::token()) . '">';
    }
}

// ------------------------------
// old() an toàn (từ Session->old)
// ------------------------------
if (!function_exists('old')) {
    function old(string $key, $default = '') {
        Session::start();
        return $_SESSION['old'][$key] ?? $default;
    }
}

// ------------------------------
// redirect helper
// ------------------------------
if (!function_exists('redirect')) {
    function redirect(string $to) {
        return Response::to($to);
    }
}

// ------------------------------
// route() — hỗ trợ route name
// ------------------------------

if (!function_exists('route')) {
    function route(string $name, array $params = []) {
        global $router;
        $path = $router->url($name, $params);
        // Prepend APP_URL/base if path is relative, so links work in subfolders
        if (!preg_match('#^https?://#i', $path)) {
            return base_url(ltrim($path, '/'));
        }
        return $path;
    }
}



// ------------------------------
// base_url() & asset() helpers
// ------------------------------
if (!function_exists('base_url')) {
    function base_url(string $path = ''): string {
        $base = rtrim(getenv('APP_URL') ?: ($_ENV['APP_URL'] ?? ''), '/');
        if ($base === '') {
            // Fallback detect from request
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $script = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
            $base = $scheme . '://' . $host . $script;
        }
        return $path ? $base . '/' . ltrim($path, '/') : $base;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        return base_url('assets/' . ltrim($path, '/'));
    }
}

// Hàm này trỏ thẳng vào thư mục gốc (public), dùng cho file Uploads
if (!function_exists('public_url')) {
    function public_url(string $path = ''): string {
        // ltrim để xóa dấu / ở đầu $path nếu có, tránh bị 2 dấu //
        return base_url(ltrim($path, '/'));
    }

    
}

