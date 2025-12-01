<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class AdminMiddleware implements MiddlewareInterface
{
    /**
     * Xử lý request: Kiểm tra quyền Admin (Role = 0)
     */
    public function handle(Request $request, callable $next): Response
    {
        // 1. Đảm bảo session đã khởi động để lấy thông tin người dùng
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Kiểm tra đăng nhập
        // Nếu chưa có user_id trong session => Chưa đăng nhập => Đá về trang login
        if (empty($_SESSION['user_id'])) {
            return Response::redirect('/login');
        }

        // 3. Lấy Role từ session
        // Ép kiểu về (int) để so sánh chính xác với số 0 trong database
        $role = isset($_SESSION['user_role']) ? (int)$_SESSION['user_role'] : -1;

        // 4. Kiểm tra quyền
        // Nếu Role KHÁC 0 (tức là không phải Admin)
        if ($role !== 0) {
            // Xử lý khi không đủ quyền:
            
            // Trường hợp 1: Nếu là Hướng dẫn viên (Role = 1), chuyển họ về trang danh sách tour của họ
            if ($role === 1) {
                return Response::redirect('/list-guide'); // Hoặc route('guide.index') tùy route bạn đặt
            }

            // Trường hợp 2: Khách hàng hoặc role lạ, chuyển về trang chủ hoặc trang đăng nhập
            // $_SESSION['flash_error'] = 'Bạn không có quyền truy cập trang quản trị.';
            return Response::redirect('/');
        }

        // 5. Nếu là Admin (Role = 0), cho phép đi tiếp vào Controller
        return $next($request);
    }
}