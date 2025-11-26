<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Support\Validator;
use App\Models\Booking;
use App\Models\Traveler;
use App\Models\Departure;
use App\Models\TourPrice;
use App\Models\Tour;
use App\Core\DB; // Giả sử bạn có class DB để gọi Transaction, nếu không thì dùng PDO từ Model

class BookingController extends BaseController
{
    // [GET] Danh sách Booking
    public function index(Request $req): Response
    {
        $bookings = (new Booking())->getAllWithDetails();
        return $this->render('booking/index', [
            'title' => 'Quản lý Đặt chỗ',
            'bookings' => $bookings
        ]);
    }

    // [GET] Xem chi tiết Booking
    public function show(Request $req): Response
    {
        $id = (int)($req->params['id'] ?? 0);
        
        // 1. Lấy thông tin Booking + Tour + Departure
        $booking = (new Booking())->builder()
            ->select('booking.*, departure.start_date, departure.end_date, tour.name as tour_name, tour.code as tour_code')
            ->join('departure', 'departure.id', '=', 'booking.departure_id')
            ->join('tour', 'tour.id', '=', 'departure.tour_id')
            ->where('booking.id', $id)
            ->first();
      
        if (!$booking) {
            $_SESSION['flash_error'] = "Không tìm thấy đơn đặt chỗ.";
            return $this->redirect(route('booking.index'));
        }
        // dd($booking);
        // 2. Lấy danh sách khách hàng
        $travelers = (new Traveler())->where('booking_id', $id);
        // dd($travelers);

        return $this->render('booking/show', [
            'booking' => $booking,
            'travelers' => $travelers,
            'title' => 'Chi tiết Booking ' . $booking['code']
        ]);
    }

    // [POST] Cập nhật trạng thái (Ví dụ: Hủy booking)
    // Router: $router->post('/booking/cancel/{id}', [BookingController::class, 'cancel']);
    public function cancel(Request $req): Response
    {
        $id = (int)($req->params['id'] ?? 0);
        $booking = (new Booking())->find($id);

        if ($booking && $booking['state'] !== 'CANCELLED') {
            try {
                // Logic cập nhật
                (new Booking())->update($id, ['state' => 'CANCELLED']);
                // Database Trigger sẽ tự động cộng lại capacity cho Departure
                
                $_SESSION['flash_success'] = "Đã hủy booking thành công. Số chỗ đã được hoàn trả.";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = "Lỗi: " . $e->getMessage();
            }
        }
        return $this->redirect(route('booking.show', ['id' => $id]));
    }

// [GET] Form đặt chỗ
    public function create(Request $req): Response
    {
        // Lấy danh sách đợt khởi hành đang MỞ (OPEN) và chưa quá hạn
        // Join thêm bảng Tour để hiện tên cho dễ chọn
        $departures = (new Departure())->builder()
            ->select('departure.*, tour.code as tour_code, tour.name as tour_name')
            ->join('tour', 'tour.id', '=', 'departure.tour_id')
            ->where('departure.status', 'OPEN')
            ->where('departure.start_date', '>=', date('Y-m-d')) 
            ->orderBy('departure.start_date', 'ASC')
            ->get();

        return $this->render('booking/create', [
            'departures' => $departures,
            'errors' => [],
            'old' => []
        ]);
    }

    // [POST] Xử lý đặt chỗ
    public function store(Request $req): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Gom dữ liệu Input
        $departureId = (int)$req->input('departure_id');
        $travelersData = $req->input('travelers') ?? []; // Mảng danh sách khách
        
        $bookingData = [
            'departure_id'  => $departureId,
            'contact_name'  => trim((string)$req->input('contact_name')),
            'contact_phone' => trim((string)$req->input('contact_phone')),
            'contact_email' => trim((string)$req->input('contact_email')),
            'note'          => trim((string)$req->input('note')),
            'pax_count'     => count($travelersData), // Tự đếm số khách
            'code'          => Booking::generateCode(),
            'state'         => 'PLACED',
            'paid_amount'   => 0
        ];

        // 2. Validate dữ liệu
        $rules = [
            'departure_id'  => 'required|exists:departure,id',
            'contact_name'  => 'required|max:255',
            'contact_phone' => 'required|max:30',
        ];
        
        $v = new Validator($bookingData, $rules, []);
        if ($v->fails() || $bookingData['pax_count'] === 0) {
            if ($bookingData['pax_count'] === 0) $_SESSION['flash_error'] = "Vui lòng nhập ít nhất 1 khách hàng.";
            
            // Load lại data để render view
            $departures = (new Departure())->builder()
                ->select('departure.*, tour.code as tour_code, tour.name as tour_name')
                ->join('tour', 'tour.id', '=', 'departure.tour_id')
                ->where('departure.status', 'OPEN')->where('departure.start_date', '>=', date('Y-m-d'))
                ->get();

            return $this->render('booking/create', [
                'departures' => $departures,
                'errors' => $v->errors(), 'old' => $bookingData, 'old_travelers' => $travelersData
            ]);
        }

        // 3. LOGIC TÍNH TIỀN (Tìm giá ADULT hiệu lực tại ngày khởi hành)
        // Lấy thông tin đợt khởi hành để biết ngày đi và tour_id
        $departure = (new Departure())->find($departureId);
        $tourId = $departure['tour_id'];
        $startDate = $departure['start_date'];

        // Tìm giá trong bảng tour_price
        $priceRecord = (new TourPrice())->builder()
            ->where('tour_id', $tourId)
            ->where('pax_type', 'ADULT') // Mặc định tính giá người lớn cho đơn giản
            ->where('effective_from', '<=', $startDate)
            ->where('effective_to', '>=', $startDate)
            ->first();

        $unitPrice = $priceRecord ? $priceRecord['base_price'] : 0;
        $bookingData['total_amount'] = $unitPrice * $bookingData['pax_count'];

        // 4. TRANSACTION & TRIGGER HANDLING
        // Nếu Base Model không hỗ trợ transaction công khai, ta dùng try-catch để rollback thủ công hoặc xử lý lỗi
        $bookingModel = new Booking();
        $bookingId = 0;

        try {
            // A. Insert Booking (Trigger fn_block_overbook sẽ chạy ở đây)
            // Nếu Capacity không đủ, DB sẽ ném Exception ngay dòng này
            $bookingId = $bookingModel->create($bookingData);

            // B. Insert Travelers
            $travelerModel = new Traveler();
            foreach ($travelersData as $t) {
                $travelerModel->create([
                    'booking_id' => $bookingId,
                    'full_name'  => $t['full_name'],
                    'gender'     => $t['gender'] ?? 'OTHER',
                    'dob'        => !empty($t['dob']) ? $t['dob'] : null,
                    'note'       => $t['note'] ?? ''
                ]);
            }

            $_SESSION['flash_success'] = "Đặt tour thành công! Mã: <b>{$bookingData['code']}</b>. Tổng tiền: " . number_format($bookingData['total_amount']);
            return $this->redirect(route('booking.show', ['id' => $bookingId]));

        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            
            // Phân tích lỗi từ Trigger Database
            if (strpos($msg, 'fn_block_overbook') !== false || strpos($msg, 'capacity') !== false) {
                $_SESSION['flash_error'] = "LỖI HẾT CHỖ: Đợt khởi hành này không còn đủ chỗ trống cho đoàn {$bookingData['pax_count']} khách.";
            } else {
                error_log("[Booking Error] " . $msg);
                $_SESSION['flash_error'] = "Lỗi hệ thống: " . $msg;
            }
            
            // Rollback thủ công: Nếu booking đã tạo nhưng traveler lỗi, xóa booking đi
            if ($bookingId > 0) {
                $bookingModel->delete($bookingId);
            }

            return $this->redirect(route('booking.create'));
        }
    }


}