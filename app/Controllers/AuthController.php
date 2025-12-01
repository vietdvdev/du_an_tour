<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;

class AuthController extends BaseController
{
    /**
     * Hiển thị form đăng nhập
     */
    public function showLogin()
    {
        // Nếu đã login rồi thì đá về trang admin
        if (!empty($_SESSION['user_id'])) {
            return $this->redirect('/admin/dashboard'); // Hoặc route mặc định của bạn
        }
        return $this->render('auth.login');
    }

    /**
     * Xử lý submit form đăng nhập
     */
    public function login(Request $req): Response
    {
        $username = trim((string)$req->input('username'));
        $password = (string)$req->input('password');

        if (!$username || !$password) {
            $_SESSION['flash_error'] = 'Vui lòng nhập đầy đủ thông tin.';
            return $this->redirect('/login');
        }

        // Tìm user trong DB
        $userModel = new User();
        // Giả sử bạn có hàm firstWhere, nếu chưa có thì dùng: ->builder()->where('username', $username)->first();
        $user = $userModel->firstWhere('username', $username);

        // Kiểm tra User tồn tại và Mật khẩu khớp
        if ($user && password_verify($password, $user['password_hash'])) {
            
            // Kiểm tra trạng thái hoạt động
            if ($user['is_active'] != 1) {
                $_SESSION['flash_error'] = 'Tài khoản này đã bị khóa.';
                return $this->redirect('/login');
            }

            // Đăng nhập thành công -> Lưu session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = (int)$user['role'];
            $_SESSION['user_name'] = $user['full_name'];

            // Điều hướng dựa trên quyền (Role)
            if ($user['role'] == 0) {
                return $this->redirect(route('admin.index')); // Vào trang Admin
            } elseif ($user['role'] == 1) {
                return $this->redirect(route('guide.index')); // Vào trang HDV
            } else {
                return $this->redirect('/'); // Khách hàng
            }
        }

        // Đăng nhập thất bại
        $_SESSION['flash_error'] = 'Tên đăng nhập hoặc mật khẩu không đúng.';
        return $this->redirect('/login');
    }

    /**
     * Đăng xuất
     */
    public function logout(): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        return $this->redirect('/login');
    }
}