<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        // 1. Start session nếu chưa có
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Kiểm tra xem có 'user_id' trong session không?
        if (empty($_SESSION['user_id'])) {
            // Lưu lại thông báo lỗi để hiện ở trang login
            $_SESSION['flash_error'] = 'Bạn cần đăng nhập để truy cập chức năng này.';
            
            // CHƯA ĐĂNG NHẬP -> ĐÁ VỀ LOGIN NGAY LẬP TỨC
            return Response::redirect('/login');
            
        }

        // 3. Đã đăng nhập -> Cho phép đi tiếp (vào check quyền hoặc vào controller)
        return $next($request);
    }
}