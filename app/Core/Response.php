<?php

namespace App\Core;

class Response
{
    protected int $status = 200;
    protected array $headers = ['Content-Type' => 'text/html; charset=utf-8'];
    protected string $content = '';

    /* ---------- Factory ---------- */

    public static function make(string $content, int $status = 200, array $headers = []): self
    {
        $r = new self();
        $r->status  = $status;
        $r->headers = array_replace($r->headers, $headers);
        $r->content = $content;
        return $r;
    }

    /** * Redirect tới URL 
     * Đã nâng cấp: Tự động thêm Base Path nếu chạy trong thư mục con
     */
    public static function redirect(string $to, int $status = 302): self
    {
        // Logic mới: Tự động thêm Base Path nếu đường dẫn bắt đầu bằng '/'
        if (str_starts_with($to, '/')) {
            // 1. Lấy đường dẫn gốc của ứng dụng (VD: /du_an_tour/public)
            // dirname($_SERVER['SCRIPT_NAME']) trả về đường dẫn chứa file index.php
            $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
            
            // 2. Nếu $to chưa chứa base path thì nối vào
            // Ví dụ: $to là '/login', $base là '/du_an_tour/public' -> Kết quả: '/du_an_tour/public/login'
            if ($base && !str_starts_with($to, $base)) {
                $to = $base . $to;
            }
        }

        $r = new self();
        $r->status  = $status;
        $r->headers = ['Location' => $to];
        $r->content = '';
        return $r;
    }

    /** Alias cho redirect */
    public static function to(string $url, int $status = 302): self
    {
        return self::redirect($url, $status);
    }

    /** Quay lại trang trước */
    public static function back(int $status = 302): self
    {
        $previous = $_SERVER['HTTP_REFERER'] ?? '/';
        // Với back(), ta không cần sửa base path vì REFERER thường đã là full URL
        return self::redirect($previous, $status);
    }

    /* ---------- Enrichers (chainable) ---------- */

    public function with(string $key, $value): self
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['flash'][$key] = $value;
        return $this;
    }

    public function withInput(array $input): self
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['old'] = $input;
        return $this;
    }

    /* ---------- JSON ---------- */

    public function json($data, int $status = 200): self
    {
        $this->status = $status;
        $this->headers['Content-Type'] = 'application/json; charset=utf-8';
        $this->content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return $this;
    }

    /* ---------- Sender ---------- */

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $k => $v) {
            header($k . ': ' . $v);
        }
        echo $this->content;
    }

    public static function route(string $name, array $params = []): self
    {
        global $router;
        // Lấy đường dẫn từ Router
        $url = $router->url($name, $params);
        
        // Gọi lại redirect để nó tự xử lý base path cho $url này
        return self::redirect($url);
    }
}