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

    /** Redirect tới URL (giữ kiểu trả về Response để Router->send() xử lý) */
    public static function redirect(string $to, int $status = 302): self
    {
        $r = new self();
        $r->status  = $status;
        $r->headers = ['Location' => $to];
        $r->content = '';
        return $r;
    }

    /** Alias cho redirect: Response::to('/users') */
    public static function to(string $url, int $status = 302): self
    {
        return self::redirect($url, $status);
    }

    /** Quay lại trang trước */
    public static function back(int $status = 302): self
    {
        $previous = $_SERVER['HTTP_REFERER'] ?? '/';
        return self::redirect($previous, $status);
    }

    /* ---------- Enrichers (chainable) ---------- */

    /** Flash message: Response::redirect('/users')->with('success','OK') */
    public function with(string $key, $value): self
    {
        Session::start();
        $_SESSION['flash'][$key] = $value;
        return $this;
    }

    /** Lưu lại input cũ để fill form: ->withInput($data) */
    public function withInput(array $input): self
    {
        Session::start();
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
        $url = $router->url($name, $params);
        return self::redirect($url);
    }
}
