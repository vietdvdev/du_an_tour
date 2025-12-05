<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Assignment;
use App\Models\GuideProfile;

class GuideController extends BaseController
{
    /**
     * Dashboard: Trang chủ của HDV
     */
      public function index(): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = $_SESSION['user_id'];
        $today  = date('Y-m-d'); // Ngày hôm nay

        $userModel = new User();
        $user = $userModel->find($userId);

        // Lấy thông tin hồ sơ
        $profile = (new GuideProfile())->firstWhere('user_id', $userId);

        // --- LOGIC MỚI: Lấy lịch trình TRONG NGÀY HÔM NAY ---
        $todayTours = (new Assignment())->builder()
            ->select('
                assignment.*, 
                departure.start_date, 
                departure.end_date as dep_end_date, 
                departure.pickup_point,
                tour.name as tour_name, 
                tour.code as tour_code
            ')
            ->join('departure', 'departure.id', '=', 'assignment.departure_id')
            ->join('tour', 'tour.id', '=', 'departure.tour_id')
            ->where('assignment.guide_id', $userId)
            // Điều kiện: Tour đang diễn ra trong ngày hôm nay
            // (Ngày bắt đầu <= Hôm nay) VÀ (Ngày kết thúc >= Hôm nay)
            ->where('departure.start_date', '<=', $today)
            ->where('departure.end_date', '>=', $today)
            ->orderBy('departure.start_date', 'ASC')
            ->get();

        // --- LOGIC THỐNG KÊ (Optional) ---
        // Đếm số tour sắp tới
        $upcomingCount = (new Assignment())->builder()
            ->join('departure', 'departure.id', '=', 'assignment.departure_id')
            ->where('assignment.guide_id', $userId)
            ->where('departure.start_date', '>', $today)
            ->count();
            
        // Đếm số tour đã dẫn
        $completedCount = (new Assignment())->builder()
            ->join('departure', 'departure.id', '=', 'assignment.departure_id')
            ->where('assignment.guide_id', $userId)
            ->where('departure.end_date', '<', $today)
            ->count();

        return $this->render('guide.dashboard', [
            'user'           => $user,
            'profile'        => $profile,
            'todayTours'     => $todayTours,      // Truyền biến này sang View
            'upcomingCount'  => $upcomingCount,
            'completedCount' => $completedCount
        ]);
    }
      /**
     * Xem lịch dẫn tour của tôi (My Tours)
     */
    public function myTours(): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $guideId = $_SESSION['user_id']; // Lấy ID của HDV đang đăng nhập

        // Truy vấn dữ liệu:
        // Lấy Assignment + join Departure (lấy ngày) + join Tour (lấy tên)
        $assignments = (new Assignment())->builder()
            ->select('
                assignment.*, 
                departure.start_date, 
                departure.end_date as dep_end_date, 
                departure.pickup_point,
                tour.name as tour_name, 
                tour.code as tour_code
            ')
            ->join('departure', 'departure.id', '=', 'assignment.departure_id')
            ->join('tour', 'tour.id', '=', 'departure.tour_id')
            ->where('assignment.guide_id', $guideId)
            ->orderBy('departure.start_date', 'DESC') // Tour mới nhất lên đầu
            ->get();

        return $this->render('guide.my_tours', [
            'assignments' => $assignments
        ]);
    }
}