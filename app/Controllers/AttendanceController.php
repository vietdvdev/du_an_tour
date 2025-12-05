<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Assignment;
use App\Models\Departure;
use App\Models\Booking;
use App\Models\Traveler;
use App\Models\Attendance;

class AttendanceController extends BaseController
{
    /**
     * [GET] Hiển thị danh sách khách để điểm danh
     */
    public function index(Request $req): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $guideId = $_SESSION['user_id'] ?? 0;
        $departureId = (int)$req->input('departure_id');

        if ($departureId <= 0) {
            return $this->redirect(route('guide.my_tours'));
        }

        // 1. Bảo mật: Kiểm tra HDV
        $assignmentCount = (new Assignment())->builder()
            ->where('guide_id', $guideId)
            ->where('departure_id', $departureId)
            ->count();

        if ($assignmentCount == 0) {
            $_SESSION['flash_error'] = "Bạn không có quyền truy cập tour này.";
            return $this->redirect(route('guide.my_tours'));
        }

        // 2. Lấy thông tin Tour/Departure
        $departure = (new Departure())->builder()
            ->select('departure.*, tour.name as tour_name, tour.code as tour_code')
            ->join('tour', 'tour.id', '=', 'departure.tour_id')
            ->where('departure.id', $departureId)
            ->first();

        // --- LOGIC KIỂM TRA THỜI GIAN (MỚI) ---
        $today = date('Y-m-d');
        $isEditable = false;
        $statusMessage = "";

        if ($today < $departure['start_date']) {
            $statusMessage = "Tour chưa khởi hành. Chức năng điểm danh chưa mở.";
        } elseif ($today > $departure['end_date']) {
            $statusMessage = "Tour đã kết thúc. Không thể chỉnh sửa điểm danh.";
        } else {
            $isEditable = true; // Chỉ cho phép khi đang trong thời gian diễn ra
        }
        // ---------------------------------------

        // 3. Lấy danh sách khách
        $travelers = (new Traveler())->builder()
            ->select('traveler.*, booking.code as booking_code, booking.contact_phone')
            ->join('booking', 'booking.id', '=', 'traveler.booking_id')
            ->where('booking.departure_id', $departureId)
            // ->where('booking.state', 'COMPLETED') 
            ->orderBy('traveler.full_name', 'ASC')
            ->get();

        // 4. Lấy dữ liệu điểm danh cũ
        $checkpoint = $req->input('checkpoint') ?? 'PICKUP';
        $attendanceLogs = (new Attendance())->builder()
            ->where('departure_id', $departureId)
            ->where('checkpoint', $checkpoint)
            ->get();

        $statusMap = [];
        foreach ($attendanceLogs as $log) {
            $statusMap[$log['traveler_id']] = $log['status'];
        }

        return $this->render('guide.attendance', [
            'departure'     => $departure,
            'travelers'     => $travelers,
            'statusMap'     => $statusMap,
            'checkpoint'    => $checkpoint,
            'isEditable'    => $isEditable,    // Truyền biến cho View
            'statusMessage' => $statusMessage  // Truyền thông báo
        ]);
    }

    /**
     * [POST] Xử lý điểm danh (AJAX)
     */
    public function checkIn(Request $req): Response
    {
        $data = [
            'departure_id' => (int)$req->input('departure_id'),
            'traveler_id'  => (int)$req->input('traveler_id'),
            'status'       => trim((string)$req->input('status')),
            'checkpoint'   => trim((string)$req->input('checkpoint')) ?: 'PICKUP',
        ];
        
        if ($data['traveler_id'] <= 0 || empty($data['status'])) {
            return $this->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
        }

        // --- BẢO MẬT: CHECK LẠI THỜI GIAN TRƯỚC KHI LƯU ---
        $dep = (new Departure())->find($data['departure_id']);
        if (!$dep) {
            return $this->json(['success' => false, 'message' => 'Không tìm thấy tour.']);
        }

        $today = date('Y-m-d');
        if ($today < $dep['start_date']) {
            return $this->json(['success' => false, 'message' => 'Lỗi: Tour chưa bắt đầu!']);
        }
        if ($today > $dep['end_date']) {
            return $this->json(['success' => false, 'message' => 'Lỗi: Tour đã kết thúc, không thể sửa đổi!']);
        }
        // --------------------------------------------------

        $model = new Attendance();
        $exists = $model->builder()
            ->where('departure_id', $data['departure_id'])
            ->where('traveler_id', $data['traveler_id'])
            ->where('checkpoint', $data['checkpoint'])
            ->first();

        $timestamp = date('Y-m-d H:i:s');

        try {
            if ($exists) {
                $model->update($exists['id'], [
                    'status'     => $data['status'],
                    'checked_at' => $timestamp
                ]);
            } else {
                $model->create([
                    'departure_id' => $data['departure_id'],
                    'traveler_id'  => $data['traveler_id'],
                    'checkpoint'   => $data['checkpoint'],
                    'status'       => $data['status'],
                    'checked_at'   => $timestamp,
                    'note'         => ''
                ]);
            }
            
            return $this->json(['success' => true, 'time' => date('H:i', strtotime($timestamp))]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
    }
}