<?php
namespace App\Controllers;


use App\Core\Request;
use App\Core\Response;
use App\Models\Payment;
use App\Models\Booking;


class PaymentController extends BaseController
{
    // [GET] Sổ quỹ: Danh sách tất cả thanh toán
    public function index(Request $req): Response
    {
        // Join với Booking để biết thu của ai
        $payments = (new Payment())->builder()
            ->select('payment.*, booking.code as booking_code, booking.contact_name')
            ->join('booking', 'booking.id', '=', 'payment.booking_id')
            ->orderBy('payment.paid_at', 'DESC')
            ->get();


        return $this->render('payment/index', [
            'title' => 'Sổ Quỹ / Lịch sử Thu',
            'payments' => $payments
        ]);
    }


   
    // [GET] Form tạo phiếu thu
    public function create(Request $req): Response
    {
        // Lấy danh sách Booking còn thiếu tiền
        $bookings = $this->getDebtBookings();


        return $this->render('payment/create', [
            'bookings' => $bookings,
            'errors'   => [], // Khởi tạo mảng lỗi rỗng
            'old'      => []  // Khởi tạo mảng dữ liệu cũ rỗng
        ]);
    }






       // [POST] Lưu phiếu thu
    public function store(Request $req): Response
    {
        // 1. Lấy dữ liệu đầu vào
        $data = [
            'booking_id' => (int)$req->input('booking_id'),
            'amount'     => (float)$req->input('amount'),
            'method'     => trim((string)$req->input('method')),
            'receipt_no' => trim((string)$req->input('receipt_no')),
            'note'       => trim((string)$req->input('note')),
        ];


        // 2. Định nghĩa Rules
        // Lưu ý: Đây là giả lập logic validate, nếu framework của bạn có $req->validate() thì dùng nó sẽ gọn hơn
        $errors = [];
       
        // --- Validate Booking ID ---
        if (empty($data['booking_id'])) {
            $errors['booking_id'] = 'Vui lòng chọn đơn hàng (Booking).';
        }


        // --- Validate Số tiền ---
        if ($data['amount'] <= 0) {
            $errors['amount'] = 'Số tiền thu phải lớn hơn 0.';
        }


        // --- Validate Mã chứng từ (Bắt buộc theo yêu cầu của bạn) ---
        if (empty($data['receipt_no'])) {
            $errors['receipt_no'] = 'Vui lòng nhập mã chứng từ hoặc số tham chiếu.';
        }


        // 3. Logic Validate Nghiệp vụ: Kiểm tra số tiền vượt quá nợ
        if (empty($errors['booking_id']) && empty($errors['amount'])) {
            $bookingModel = new Booking();
            $booking = $bookingModel->find($data['booking_id']);


            if (!$booking) {
                $errors['booking_id'] = 'Đơn hàng không tồn tại.';
            } else {
                $total  = (float)($booking['total_amount'] ?? 0);
                $paid   = (float)($booking['paid_amount'] ?? 0);
                $remain = $total - $paid;


                // Cho phép sai số nhỏ (epsilon)
                if ($data['amount'] > ($remain + 100)) {
                    $errors['amount'] = "Số tiền vượt quá số nợ (" . number_format($remain) . " đ).";
                }
            }
        }


        // 4. Nếu có lỗi => Trả về View cùng thông báo lỗi và dữ liệu cũ
        if (!empty($errors)) {
            // Lấy lại danh sách booking để hiển thị lại select box
            $bookings = $this->getDebtBookings();


            return $this->render('payment/create', [
                'bookings' => $bookings,
                'errors'   => $errors, // Mảng chứa lỗi cụ thể
                'old'      => $data    // Dữ liệu người dùng vừa nhập
            ]);
        }


        try {
            // 5. Nếu không có lỗi => Lưu vào DB
            (new Payment())->create([
                'booking_id' => $data['booking_id'],
                'amount'     => $data['amount'],
                'method'     => $data['method'],
                'receipt_no' => $data['receipt_no'],
                'note'       => $data['note'],
                'paid_at'    => date('Y-m-d H:i:s')
            ]);


            // 6. Tính lại tổng tiền booking
            if (method_exists($this, 'recalcBooking')) {
                $this->recalcBooking($data['booking_id']);
            } elseif (class_exists(BookingFinanceController::class)) {
               
            }


            $_SESSION['flash_success'] = "Tạo phiếu thu thành công.";
            return $this->redirect(route('payment.index'));


        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi hệ thống: " . $e->getMessage();
            return $this->redirect(route('payment.create'));
        }
    }


    /**
     * Hàm phụ trợ lấy danh sách booking còn nợ
     */
    private function getDebtBookings()
    {
        $allBookings = (new Booking())->builder()
            ->where('state', '!=', 'CANCELLED')
            ->orderBy('id', 'DESC')
            ->get();


        $bookings = [];
        foreach ($allBookings as $bk) {
            $total = (float)($bk['total_amount'] ?? 0);
            $paid  = (float)($bk['paid_amount'] ?? 0);
            if ($total > $paid) {
                $bookings[] = $bk;
            }
        }
        return $bookings;
    }
    /**
     * Logic cập nhật trạng thái booking sau khi thu tiền
     */
    private function recalcBooking($bookingId) {
        // Tính tổng đã thu
        $paid = (new Payment())->builder()
            ->select('SUM(amount) as total')
            ->where('booking_id', $bookingId)
            ->first();
        $totalPaid = (float)($paid['total'] ?? 0);


        // Lấy tổng phải thu
        $bk = (new Booking())->find($bookingId);
        $totalAmount = (float)$bk['total_amount'];


        // Update trạng thái
        $newState = $bk['state'];
        if ($totalPaid >= $totalAmount && $totalAmount > 0) $newState = 'COMPLETED';
        elseif ($totalPaid > 0) $newState = 'DEPOSITED';


        (new Booking())->update($bookingId, [
            'paid_amount' => $totalPaid,
            'state' => $newState
        ]);
    }
}

