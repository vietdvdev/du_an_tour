<?php
namespace App\Controllers;

// Import các lớp cần thiết (Models, Core components)
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

/**
 * Class BookingController
 * Xử lý các nghiệp vụ liên quan đến Đặt chỗ (Booking)
 */
class BookingController extends BaseController
{

    public function index(Request $req): Response
    {
        // 1. Lấy tham số lọc từ URL (query string)
        $type = $_GET['type'] ?? 'all'; // Lọc theo trạng thái (Ví dụ: all, placed, confirmed, cancelled...)
        $time = $_GET['time'] ?? 'all'; // Lọc theo thời gian

        // 2. Gọi hàm vừa viết trong Model để lấy dữ liệu đã lọc
        $bookingModel = new Booking();
        $bookings = $bookingModel->getFilteredBookings($type, $time); // Giả định Model có phương thức này
       
        // 3. Render View để hiển thị danh sách
        return $this->render('booking/index', [
            'title' => 'Quản lý Đặt chỗ',
            'bookings' => $bookings,
            'filterType' => $type, // Truyền lại để view hiển thị trạng thái active cho nút lọc
            'filterTime' => $time
        ]);
    }


    public function show(Request $req): Response
    {
        // Lấy ID của booking từ tham số URL
        $id = (int)($req->params['id'] ?? 0);
        
        // 1. Lấy thông tin Booking chính + thông tin liên quan (Departure, Tour)
        $booking = (new Booking())->builder()
            // Chọn các cột cần thiết, bao gồm thông tin Tour và Departure
            ->select('booking.*, departure.start_date, departure.end_date, tour.name as tour_name, tour.code as tour_code, tour.is_custom')
            ->join('departure', 'departure.id', '=', 'booking.departure_id')
            ->join('tour', 'tour.id', '=', 'departure.tour_id')
            ->where('booking.id', $id)
            ->first(); // Lấy 1 record duy nhất

        if (!$booking) {
            // Xử lý khi không tìm thấy booking
            $_SESSION['flash_error'] = "Không tìm thấy đơn đặt chỗ.";
            return $this->redirect(route('booking.index'));
        }

        // 2. Lấy dữ liệu các bảng con (một-nhiều)
        $travelers = (new Traveler())->where('booking_id', $id); // Danh sách khách đi tour
        
        // Lấy danh sách dịch vụ đi kèm (Nếu lớp BookingService tồn tại)
        $services = class_exists(BookingService::class) ? (new BookingService())->where('booking_id', $id) : [];
        // Lấy danh sách các giao dịch thanh toán (Nếu lớp Payment tồn tại)
        $payments = class_exists(Payment::class) ? (new Payment())->where('booking_id', $id) : [];
        
        // 3. Lấy lịch sử thay đổi trạng thái (Log)
        $logs = [];
        try {
            if (class_exists(BookingLog::class)) {
                $logs = (new BookingLog())->builder()
                        ->where('booking_id', $id)
                        ->orderBy('created_at', 'DESC')
                        ->get();
            }
        } catch (\Throwable $e) {
            // Bỏ qua lỗi nếu chưa có bảng log (ví dụ: đang phát triển)
        }

        // Render View chi tiết
        return $this->render('booking/show', [
            'booking'   => $booking,
            'travelers' => $travelers,
            'services'  => $services,
            'payments'  => $payments,
            'logs'      => $logs,
            'title'     => 'Chi tiết Booking ' . $booking['code']
        ]);
    }

 
    public function cancel(Request $req): Response
    {
        $id = (int)($req->params['id'] ?? 0);
        $booking = (new Booking())->find($id);

        // Chỉ hủy nếu Booking tồn tại và chưa bị hủy
        if ($booking && $booking['state'] !== 'CANCELLED') {
            try {
                // Cập nhật trạng thái
                (new Booking())->update($id, ['state' => 'CANCELLED']);
                // Ghi chú: Logic hoàn trả chỗ trống cho Departure thường được xử lý bằng Database Trigger.
                
                $_SESSION['flash_success'] = "Đã hủy booking thành công. Số chỗ đã được hoàn trả.";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = "Lỗi: " . $e->getMessage();
            }
        }
        // Chuyển hướng lại trang chi tiết booking
        return $this->redirect(route('booking.show', ['id' => $id]));
    }



    public function create(Request $req): Response
    {
        // Lấy danh sách đợt khởi hành hợp lệ để khách hàng chọn
        $departures = (new Departure())->builder()
            // Join với Tour để lấy thông tin Tour
            ->select('departure.*, tour.code as tour_code, tour.name as tour_name, tour.state as tour_state, tour.is_custom as tour_custom ')
            ->join('tour', 'tour.id', '=', 'departure.tour_id')
            ->where('departure.status', 'OPEN')       // Lịch đang mở
            ->where('tour.state', 'PUBLISHED')        // Tour đã công bố
            ->where('tour.is_custom', 0)              // Chỉ lấy tour hệ thống (không phải tour thiết kế riêng)
            ->where('departure.start_date', '>=', date('Y-m-d')) // Chưa quá hạn
            ->orderBy('departure.start_date', 'ASC')
            ->get();       
            
        return $this->render('booking/create', [
            'departures' => $departures,
            'errors'     => [],
            'old'        => [] // Dữ liệu cũ (nếu validate thất bại)
        ]);
    }

    /**
     * [POST] Xử lý lưu Booking mới vào hệ thống
     * * @param Request $req
     * @return Response
     */
    public function store(Request $req): Response
    {
        // Đảm bảo session được khởi động
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Gom dữ liệu Input
        $departureId   = (int)$req->input('departure_id');
        $travelersData = $req->input('travelers') ?? []; // Mảng thông tin khách đi tour
        
        // Tạo mã Booking ngẫu nhiên: BK-YYYYMMDD-XXXX
        $bookingCode = 'BK-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 4));

        $bookingData = [
            'departure_id'  => $departureId,
            'contact_name'  => trim((string)$req->input('contact_name')),
            'contact_phone' => trim((string)$req->input('contact_phone')),
            'contact_email' => trim((string)$req->input('contact_email')),
            'note'          => trim((string)$req->input('note')),
            'pax_count'     => count($travelersData), // Số lượng khách
            'code'          => $bookingCode,
            'state'         => 'PLACED', // Trạng thái: Mới đặt (Chờ xác nhận)
            'paid_amount'   => 0
        ];

        // 2. Validate dữ liệu cơ bản (thông tin liên hệ)
        $rules = [
            'departure_id'  => 'required',
            'contact_name'  => 'required|max:255',
            'contact_phone' => 'required|max:30',
        ];
        
        $v = new Validator($bookingData, $rules);
        if ($v->fails()) {
            // Nếu validate thất bại, load lại form và hiển thị lỗi
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
                // Dùng all() để lấy toàn bộ dữ liệu, kể cả mảng travelers
                'old'        => $req->all() 
            ]);
        }
    
        // 3. LOGIC TÍNH TỔNG TIỀN (Dựa trên bảng giá TourPrice)
        try {
            // Lấy thông tin Departure và Tour
            $departure = (new Departure())->find($departureId);
            $tourId    = $departure['tour_id'];
            $startDate = $departure['start_date']; 

            // Lấy tất cả bảng giá TourPrice đang hiệu lực tại ngày khởi hành
            $priceRecords = (new TourPrice())->builder()
                ->where('tour_id', $tourId)
                ->where('effective_from', '<=', $startDate)
                ->where('effective_to', '>=', $startDate)
                ->get();

            $priceMap = []; // Map loại khách (ADULT, CHILD, INFANT) với giá
            foreach ($priceRecords as $p) {
                $priceMap[$p['pax_type']] = (float)$p['base_price'];
            }

            $totalAmount = 0;
            
            // Lặp qua từng khách để tính tuổi và áp dụng giá
            foreach ($travelersData as $t) {
                $dob = $t['dob'] ?? null;
                $paxType = 'ADULT'; 

                if (!empty($dob)) {
                    // Hàm tính tuổi (định nghĩa bên dưới)
                    $age = $this->calculateAge($dob, $startDate); 
                    if ($age < 2) {
                        $paxType = 'INFANT';
                    } elseif ($age < 12) {
                        $paxType = 'CHILD';
                    } else {
                        $paxType = 'ADULT';
                    }
                }

                // Lấy giá, nếu không tìm thấy giá cho loại pax đó, sẽ ưu tiên dùng giá ADULT, sau đó là 0
                $unitPrice = $priceMap[$paxType] ?? ($priceMap['ADULT'] ?? 0);
                $totalAmount += $unitPrice;
            }

            $bookingData['total_amount'] = $totalAmount;

            // 4. LƯU VÀO DATABASE (Nên dùng Transaction để đảm bảo tính toàn vẹn)
            // (Hiện tại code không dùng Transaction rõ ràng, giả sử Model hoặc DB đã có xử lý)
            $bookingModel = new Booking();
            $bookingId = $bookingModel->create($bookingData); // Lưu Booking chính

            // Lưu danh sách khách (Traveler)
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
            // Ghi chú: Việc giảm capacity của Departure cũng thường được xử lý bằng Database Trigger.

            // Thông báo thành công và chuyển hướng đến trang chi tiết
            $_SESSION['flash_success'] = "Đặt tour thành công! Mã: <strong>{$bookingCode}</strong>. Tổng tiền: " . number_format($totalAmount) . " đ";
            return $this->redirect(route('booking.show', ['id' => $bookingId]));

        } catch (\Throwable $e) {
            // Xử lý lỗi (ví dụ: lỗi database, lỗi quá tải chỗ/overbook)
            $msg = $e->getMessage();
            
            // Xử lý lỗi Trigger database (nếu có logic check overbook)
            if (strpos($msg, 'fn_block_overbook') !== false || strpos($msg, 'capacity') !== false) {
                $_SESSION['flash_error'] = "LỖI: Đợt khởi hành này đã hết chỗ!";
            } else {
                $_SESSION['flash_error'] = "Lỗi hệ thống: " . $msg;
            }
            
            // Nếu lỗi xảy ra sau khi tạo Booking (chẳng hạn lỗi lưu Traveler), có thể cần xóa Booking vừa tạo (rollback thủ công)
            // if (isset($bookingId) && $bookingId > 0) $bookingModel->delete($bookingId);

            return $this->redirect(route('booking.create')); // Quay lại form tạo
        }
    }


    private function calculateAge($dob, $atDate)
    {
        $birthDate = new \DateTime($dob);
        $targetDate = new \DateTime($atDate);
        $interval = $birthDate->diff($targetDate);
        return $interval->y; // Trả về số năm (tuổi)
    }

 
    public function updateStatus(Request $req): Response
    {
        $id = (int)($req->params['id'] ?? 0);
        $newState = $req->input('status'); // Trạng thái mới
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
        // QUY TẮC 1: Cấm thay đổi nếu đã HOÀN TẤT
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
            // Logic cho các trạng thái khác có thể được thêm vào đây
        }

        // QUY TẮC 3: Ngăn chặn nhảy cóc trạng thái phi logic (ví dụ: chuyển sang COMPLETED mà chưa đủ tiền)
        // ... (Logic kiểm tra thanh toán, v.v.)
        // --- KẾT THÚC LOGIC ---

        try {
            // Cập nhật Booking
            $bookingModel->update($id, ['state' => $newState]);

            // Ghi Log Lịch sử thay đổi trạng thái
            BookingLog::record($id, $oldState, $newState, $note, 'Admin'); // Giả định BookingLog có hàm record tĩnh

            // Logic xử lý phụ (nếu Hủy thì trả lại chỗ trống) - Database Trigger thường đã lo việc này
            // Nếu không có Trigger, cần gọi hàm/logic cập nhật Capacity ở đây.

            $_SESSION['flash_success'] = "Đã chuyển trạng thái từ <b>$oldState</b> sang <b>$newState</b>.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi hệ thống: " . $e->getMessage();
        }

        return $this->redirect(route('booking.show', ['id' => $id]));
    }


    public function addTraveler(Request $req): Response
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $bookingId = (int)$req->params['id'];

        // 1. Lấy dữ liệu từ Form
        $fullName = trim((string)$req->input('full_name'));
        $gender   = $req->input('gender');
        $dob      = $req->input('dob'); // Y-m-d
        $note     = trim((string)$req->input('note'));
        
        // Giá phát sinh nhập tay (Chỉ dùng cho Custom Tour)
        $manualExtraPrice = (float)str_replace(',', '', $req->input('extra_price') ?? 0);

        // 2. Validate cơ bản
        if (empty($fullName)) {
            $_SESSION['flash_error'] = "Vui lòng nhập họ tên khách.";
            return $this->redirect(route('booking.show', ['id' => $bookingId]));
        }

        try {
            $bookingModel = new Booking();
            // Lấy thông tin Booking + Tour (để biết là Custom hay System) + Departure (để lấy start_date)
            $booking = $bookingModel->builder()
                ->select('booking.*, tour.is_custom, tour.id as tour_id, departure.start_date')
                ->join('departure', 'departure.id', '=', 'booking.departure_id')
                ->join('tour', 'tour.id', '=', 'departure.tour_id')
                ->where('booking.id', $bookingId)
                ->first();

            if (!$booking) {
                $_SESSION['flash_error'] = "Không tìm thấy booking.";
                return $this->redirect(route('booking.index'));
            }

            // A. Thêm khách vào bảng traveler
            (new Traveler())->create([
                'booking_id' => $bookingId,
                'full_name'  => $fullName,
                'gender'     => $gender,
                'dob'        => !empty($dob) ? $dob : null,
                'note'       => $note
            ]);
            // Ghi chú: Database Trigger có thể tự động giảm capacity của Departure ở đây.

            // B. Tính toán số tiền cần cộng thêm
            $priceToAdd = 0;
            $msgMoney = "";

            if ($booking['is_custom'] == 1) {
                // TRƯỜNG HỢP 1: TOUR CUSTOM -> Dùng giá nhập tay
                $priceToAdd = $manualExtraPrice;
            } else {
                // TRƯỜNG HỢP 2: TOUR HỆ THỐNG -> Tự tính theo bảng giá
                $paxType = 'ADULT'; // Mặc định
                
                // Tính tuổi để xác định loại khách
                if (!empty($dob)) {
                    $age = $this->calculateAge($dob, $booking['start_date']);
                    if ($age < 2) $paxType = 'INFANT';
                    elseif ($age < 12) $paxType = 'CHILD';
                }

                // Tra cứu giá trong bảng tour_price
                $priceRecord = (new TourPrice())->builder()
                    ->where('tour_id', $booking['tour_id'])
                    ->where('pax_type', $paxType)
                    ->first();

                if ($priceRecord) {
                    $priceToAdd = (float)$priceRecord['base_price'];
                } else {
                    // Fallback: Nếu không tìm thấy giá loại pax cụ thể, thử tìm giá Người lớn (ADULT)
                    $fallbackPrice = (new TourPrice())->builder()
                        ->where('tour_id', $booking['tour_id'])
                        ->where('pax_type', 'ADULT')
                        ->first();
                    $priceToAdd = $fallbackPrice ? (float)$fallbackPrice['base_price'] : 0;
                }
            }

            // C. Cập nhật Booking (Tăng số lượng khách và Tổng tiền)
            $newPaxCount = $booking['pax_count'] + 1;
            $newTotalAmount = $booking['total_amount'] + $priceToAdd;

            $bookingModel->update($bookingId, [
                'pax_count'      => $newPaxCount,
                'total_amount' => $newTotalAmount
            ]);

            // Thông báo kết quả
            if ($priceToAdd > 0) {
                $msgMoney = " Đã cộng thêm <b>" . number_format($priceToAdd) . " đ</b> vào đơn hàng.";
            } else {
                $msgMoney = " Không phát sinh thêm chi phí.";
            }

            $_SESSION['flash_success'] = "Đã thêm khách <b>$fullName</b> thành công." . $msgMoney;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi hệ thống: " . $e->getMessage();
        }

        return $this->redirect(route('booking.show', ['id' => $bookingId]));
    }
}