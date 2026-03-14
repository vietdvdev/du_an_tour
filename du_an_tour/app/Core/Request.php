<?php
namespace App\Core;

class Request
{
    public string $method;
    public string $uri;      // <-- đường dẫn đã bỏ base, ví dụ "/"
    public array $query;
    public array $body;
    public array $headers;
    public array $params = [];
    public array $cookies;

    public static function capture(): self
    {
        Session::start();

        $req = new self();
        $req->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // 1) Lấy URI gốc
        $rawUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        // 2) Tính base path
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        // 3) Bỏ base path khỏi URI
        if ($base && $base !== '/' && str_starts_with($rawUri, $base)) {
            $rawUri = substr($rawUri, strlen($base));
            if ($rawUri === '' || $rawUri[0] !== '/') {
                $rawUri = '/' . ltrim($rawUri, '/');
            }
        }

        // 4) Chuẩn hoá các phần
        $req->uri     = rtrim($rawUri, '/') ?: '/';
        $req->query   = $_GET;
        $req->body    = $_POST;
        $req->headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];
        $req->cookies = $_COOKIE;

        return $req;
    }

    /** Lấy 1 field cụ thể */
    public function input(string $key, $default = null)
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /** Lấy toàn bộ input (body + query + params) */
    public function all(): array
    {
        return array_merge($this->params, $this->query, $this->body);
    }

    /** Kiểm tra tồn tại field */
    public function has(string $key): bool
    {
        $all = $this->all();
        return array_key_exists($key, $all);
    }

    /** Lấy một số field cụ thể */
    public function only(array $keys): array
    {
        $all = $this->all();
        $out = [];
        foreach ($keys as $k) {
            if (array_key_exists($k, $all)) {
                $out[$k] = $all[$k];
            }
        }
        return $out;
    }

    /** Lấy dữ liệu ngoại trừ một số field */
    public function except(array $keys): array
    {
        $all = $this->all();
        foreach ($keys as $k) unset($all[$k]);
        return $all;
    }
}
