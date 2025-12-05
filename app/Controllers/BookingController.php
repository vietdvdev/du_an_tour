<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Support\Validator;
use App\Models\Booking;
use App\Models\Traveler;
use App\Models\Departure;
use App\Models\TourPrice;
use App\Models\BookingLog;
use App\Models\BookingService;
use App\Models\Payment;
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
        
        // 1. Lấy thông tin Booking
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

        // 2. Lấy dữ liệu các bảng con (Chuẩn bị sẵn cho View)
        $travelers = (new Traveler())->where('booking_id', $id);
        $services  = (new BookingService())->where('booking_id', $id);
        $payments  = (new Payment())->where('booking_id', $id);
        
        // Lấy lịch sử (Nếu chưa có model BookingLog thì bỏ dòng này hoặc tạo Model như hướng dẫn trước)
        $logs = [];
        try {
            $logs = (new \App\Models\BookingLog())
                    ->builder()
                    ->where('booking_id', $id)
                    ->orderBy('created_at', 'DESC')
                    ->get();
        } catch (\Throwable $e) {
            // Bỏ qua nếu chưa tạo bảng log
        }

        return $this->render('booking/show', [
            'booking'   => $booking,
            'travelers' => $travelers,
            'services'  => $services,  // Truyền biến này sang View
            'payments'  => $payments,  // Truyền biến này sang View
            'logs'      => $logs,      // Truyền biến này sang View
            'title'     => 'Chi tiết Booking ' . $booking['code']
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
        // Lấy danh sách đợt khởi hành hợp lệ
        $departures = (new Departure())->builder()
            ->select('departure.*, tour.code as tour_code, tour.name as tour_name, tour.state as tour_state')
            ->join('tour', 'tour.id', '=', 'departure.tour_id')
            ->where('departure.status', 'OPEN')       // Lịch đang mở
            ->where('tour.state', 'PUBLISHED')        // Tour đã công bố
            ->where('departure.start_date', '>=', date('Y-m-d')) // Chưa quá hạn
            ->orderBy('departure.start_date', 'ASC')
            ->get();

        return $this->render('booking/create', [
            'departures' => $departures,
            'errors'     => [],
            'old'        => []
        ]);
    }

  public function store(Request $req): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Gom dữ liệu Input
        $departureId   = (int)$req->input('departure_id');
        $travelersData = $req->input('travelers') ?? [];
        
        // Tạo mã Booking ngẫu nhiên: BK-YYYYMMDD-XXXX
        $bookingCode = 'BK-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 4));

        $bookingData = [
            'departure_id'  => $departureId,
            'contact_name'  => trim((string)$req->input('contact_name')),
            'contact_phone' => trim((string)$req->input('contact_phone')),
            'contact_email' => trim((string)$req->input('contact_email')),
            'note'          => trim((string)$req->input('note')),
            'pax_count'     => count($travelersData),
            'code'          => $bookingCode,
            'state'         => 'PLACED', // Mới đặt
            'paid_amount'   => 0
        ];

        // 2. Validate dữ liệu cơ bản
        $rules = [
            'departure_id'  => 'required',
            'contact_name'  => 'required|max:255',
            'contact_phone' => 'required|max:30',
        ];
        
        $v = new Validator($bookingData, $rules);
        if ($v->fails()) {
            // Load lại danh sách departure để view không bị lỗi khi reload
            $departures = (new Departure())->builder()
                ->select('departure.*, tour.code as tour_code, tour.name as tour_name')
                ->join('tour', 'tour.id', '=', 'departure.tour_id')
                ->where('departure.status', 'OPEN')
                ->where('tour.state', 'PUBLISHED') 
                ->where('departure.start_date', '>=', date('Y-m-d'))
                ->orderBy('departure.start_date', 'ASC')
                ->get();

            return $this->render('booking/create', [
                'departures' => $departures,
                'errors'     => $v->errors(),
                // SỬA LỖI: Dùng all() thay vì input() để lấy toàn bộ dữ liệu
                'old'        => $req->all() 
            ]);
        }
    
        // 3. LOGIC TÍNH TIỀN (Dựa trên bảng giá TourPrice)
        try {
            $departure = (new Departure())->find($departureId);
            $tourId    = $departure['tour_id'];
            $startDate = $departure['start_date']; 

            // Lấy bảng giá hiệu lực
            $priceRecords = (new TourPrice())->builder()
                ->where('tour_id', $tourId)
                ->where('effective_from', '<=', $startDate)
                ->where('effective_to', '>=', $startDate)
                ->get();

            $priceMap = [];
            foreach ($priceRecords as $p) {
                $priceMap[$p['pax_type']] = (float)$p['base_price'];
            }

            $totalAmount = 0;
            
            foreach ($travelersData as $t) {
                $dob = $t['dob'] ?? null;
                $paxType = 'ADULT'; 

                if (!empty($dob)) {
                    $age = $this->calculateAge($dob, $startDate);
                    if ($age < 2) {
                        $paxType = 'INFANT';
                    } elseif ($age < 12) {
                        $paxType = 'CHILD';
                    } else {
                        $paxType = 'ADULT';
                    }
                }

                // Lấy giá, fallback về ADULT hoặc 0 nếu không tìm thấy
                $unitPrice = $priceMap[$paxType] ?? ($priceMap['ADULT'] ?? 0);
                $totalAmount += $unitPrice;
            }

            $bookingData['total_amount'] = $totalAmount;

            // 4. LƯU VÀO DATABASE
            $bookingModel = new Booking();
            $bookingId = $bookingModel->create($bookingData);

            // Lưu danh sách khách
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

            $_SESSION['flash_success'] = "Đặt tour thành công! Mã: <strong>{$bookingCode}</strong>. Tổng tiền: " . number_format($totalAmount) . " đ";
            return $this->redirect(route('booking.show', ['id' => $bookingId]));

        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            // Xử lý lỗi Trigger database (nếu có logic check overbook)
            if (strpos($msg, 'fn_block_overbook') !== false || strpos($msg, 'capacity') !== false) {
                $_SESSION['flash_error'] = "LỖI: Đợt khởi hành này đã hết chỗ!";
            } else {
                $_SESSION['flash_error'] = "Lỗi hệ thống: " . $msg;
            }
            
            // Nếu lỗi khi lưu khách, có thể cần xóa booking vừa tạo (rollback thủ công)
            // if (isset($bookingId) && $bookingId > 0) $bookingModel->delete($bookingId);

            return $this->redirect(route('booking.create'));
        }
    }

        /**
     * Hàm phụ trợ tính tuổi chính xác theo ngày
     */
    private function calculateAge($dob, $atDate)
    {
        $birthDate = new \DateTime($dob);
        $targetDate = new \DateTime($atDate);
        $interval = $birthDate->diff($targetDate);
        return $interval->y; // Trả về số năm
    }

    // [POST] Cập nhật trạng thái thủ công (Có kiểm tra quy tắc nghiệp vụ)
    public function updateStatus(Request $req): Response
    {
        $id = (int)($req->params['id'] ?? 0);
        $newState = $req->input('status');
        $note = trim((string)$req->input('note'));

        $bookingModel = new Booking();
        $booking = $bookingModel->find($id);

        if (!$booking) {
            $_SESSION['flash_error'] = "Không tìm thấy đơn hàng.";
            return $this->redirect(route('booking.index'));
        }

        $oldState = $booking['state'];

        // Nếu trạng thái không đổi thì không làm gì
        if ($oldState === $newState) {
            return $this->redirect(route('booking.show', ['id' => $id]));
        }

        // --- BẮT ĐẦU LOGIC KIỂM TRA (BUSINESS RULES) ---

        // QUY TẮC 1: Nếu đã HOÀN TẤT (COMPLETED), cấm chỉnh sửa bất cứ thứ gì
        if ($oldState === 'COMPLETED') {
            $_SESSION['flash_error'] = "❌ Lỗi: Đơn hàng đã <b>HOÀN TẤT</b>. Không thể thay đổi trạng thái nữa.";
            return $this->redirect(route('booking.show', ['id' => $id]));
        }

        // QUY TẮC 2: Kiểm tra điều kiện HỦY (CANCELLED)
        if ($newState === 'CANCELLED') {
            // Chỉ cho phép hủy nếu đang ở trạng thái 'PLACED' (Chờ xác nhận)
            if ($oldState === 'DEPOSITED') {
                $_SESSION['flash_error'] = "❌ Lỗi: Khách hàng đã <b>ĐẶT CỌC</b>. Không thể hủy ngay (Cần xử lý hoàn tiền trước).";
                return $this->redirect(route('booking.show', ['id' => $id]));
            }
            
            // (Trường hợp COMPLETED đã bị chặn ở Quy tắc 1 rồi)
        }

        // QUY TẮC 3: Ngăn chặn nhảy cóc trạng thái phi logic (Tùy chọn thêm)
        // Ví dụ: Không thể chuyển từ PLACED thẳng sang COMPLETED nếu chưa trả đủ tiền
        // if ($newState === 'COMPLETED' && $booking['paid_amount'] < $booking['total_amount']) {
        //    $_SESSION['flash_error'] = "Chưa thanh toán đủ, không thể hoàn tất.";
        //    return ...
        // }

        // --- KẾT THÚC LOGIC ---

        try {
            // Cập nhật Booking
            $bookingModel->update($id, ['state' => $newState]);

            // Ghi Log Lịch sử
            BookingLog::record($id, $oldState, $newState, $note, 'Admin');

            // Logic xử lý phụ (nếu Hủy thì trả lại chỗ trống) - Database Trigger thường đã lo việc này
            // Nhưng nếu chưa có Trigger, bạn cần gọi hàm update Capacity ở đây.

            $_SESSION['flash_success'] = "Đã chuyển trạng thái từ <b>$oldState</b> sang <b>$newState</b>.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi hệ thống: " . $e->getMessage();
        }

        return $this->redirect(route('booking.show', ['id' => $id]));
    }




}