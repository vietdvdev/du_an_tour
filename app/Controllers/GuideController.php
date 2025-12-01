<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\GuideProfile;

class GuideController extends BaseController
{
    /**
     * Dashboard: Trang chủ của HDV
     */
    public function index(): Response
    {
        // Lấy thông tin user đang đăng nhập
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = $_SESSION['user_id'];

        $userModel = new User();
        $user = $userModel->find($userId);

        // Lấy thông tin hồ sơ (nếu có)
        $profile = (new GuideProfile())->firstWhere('user_id', $userId);

        return $this->render('guide.dashboard', [
            'user' => $user,
            'profile' => $profile
        ]);
    }
}