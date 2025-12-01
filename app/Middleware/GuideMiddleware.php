<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class GuideMiddleware implements MiddlewareInterface
{
    /**
     * Xử lý request: Kiểm tra quyền Hướng dẫn viên (Role = 1)
     */
    public function handle(Request $request, callable $next): Response
    {
        // 1. Đảm bảo session đã khởi động
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Kiểm tra đăng nhập
        // Nếu chưa có user_id => Chưa login => Đá về trang đăng nhập
        if (empty($_SESSION['user_id'])) {
            return Response::redirect('/login');
        }

        // 3. Lấy Role từ session
        // Ép kiểu về (int) để so sánh chính xác với số 1
        $role = isset($_SESSION['user_role']) ? (int)$_SESSION['user_role'] : -1;

        // 4. Kiểm tra quyền
        // Nếu Role KHÁC 1 (tức là không phải HDV)
        if ($role !== 1) {
            // Xử lý khi không đủ quyền:
            
            // Trường hợp 1: Nếu là Admin (Role = 0), có thể cho phép truy cập hoặc đá về trang Admin
            if ($role === 0) {
                // Tùy chọn: Chuyển hướng Admin về trang quản trị của họ
                return Response::redirect('/list-admin');
            }

            // Trường hợp 2: Khách hàng hoặc tài khoản khác => Đá về trang chủ
            return Response::redirect('/');
        }

        // 5. Nếu đúng là HDV (Role = 1), cho phép đi tiếp
        return $next($request);
    }
}