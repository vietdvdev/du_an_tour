<?php

namespace App\Controllers;

use App\Core\View;
use App\Core\Response;

class BaseController
{
    // protected function view(string $view, array $data = []): Response
    // {
    //     return Response::make(View::render($view, $data));
    // }

    protected function json($data, int $status = 200): Response
    {
        return (new Response())->json($data, $status);
    }

    protected function redirect(string $to): Response
    {
        return Response::redirect($to);
    }

    public function render(string $view, array $data = []): Response
    {
        // xác định đường dẫn view
        $file = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($file)) {
            return Response::make("View not found: $view", 404);
        }

        // đưa dữ liệu từ mảng $data ra thành biến
        extract($data);

        // bắt output buffer
        ob_start();
        include $file;

        // trả HTML vào Response để router xử lý
        return Response::make(ob_get_clean());
    }
}
