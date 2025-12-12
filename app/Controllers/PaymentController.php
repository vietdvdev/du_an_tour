<?php
namespace App\Controllers;

// Import các lớp cần thiết
use App\Core\Request;
use App\Core\Response;
use App\Models\Payment; // Model cho Phiếu thu/Thanh toán
use App\Models\Booking; // Model cho Booking (Đơn đặt chỗ)

/**
 * Class PaymentController
 * Xử lý các nghiệp vụ liên quan đến Quản lý Phiếu thu (Sổ quỹ).
 */
class PaymentController extends BaseController
{
    /**
     * [GET] Sổ quỹ: Danh sách tất cả thanh toán đã ghi nhận.
     * @param Request $req
     * @return Response
     */
    public function index(Request $req): Response
    {
        // Join với Booking để hiển thị thông tin đơn hàng liên quan
        $payments = (new Payment())->builder()
            ->select('payment.*, booking.code as booking_code, booking.contact_name')
            ->join('booking', 'booking.id', '=', 'payment.booking_id')
            ->orderBy('payment.paid_at', 'DESC') // Sắp xếp theo thời gian thanh toán mới nhất
            ->get();

        return $this->render('payment/index', [
            'title' => 'Sổ Quỹ / Lịch sử Thu',
            'payments' => $payments
        ]);
    }

    
    /**
     * [GET] Hiển thị Form tạo phiếu thu mới.
     * @param Request $req
     * @return Response
     */
    public function create(Request $req): Response
    {
        // Lấy danh sách Booking còn thiếu tiền để người dùng chọn
        $bookings = $this->getDebtBookings();

        return $this->render('payment/create', [
            'bookings' => $bookings,
            'errors'   => [], // Khởi tạo mảng lỗi rỗng
            'old'      => []  // Khởi tạo mảng dữ liệu cũ rỗng
        ]);
    }


    /**
     * [POST] Xử lý lưu Phiếu thu mới vào hệ thống.
     * @param Request $req
     * @return Response
     */
    public function store(Request $req): Response
    {
        // 1. Lấy dữ liệu đầu vào
        $data = [
            'booking_id' => (int)$req->input('booking_id'),
            'amount'     => (float)$req->input('amount'),
            'method'     => trim((string)$req->input('method')),
            'receipt_no' => trim((string)$req->input('receipt_no')), // Mã chứng từ/tham chiếu
            'note'       => trim((string)$req->input('note')),
        ];

        // 2. Định nghĩa và chạy Rules Validate thủ công
        $errors = [];
        
        // --- Validate Booking ID ---
        if (empty($data['booking_id'])) {
            $errors['booking_id'] = 'Vui lòng chọn đơn hàng (Booking).';
        }

        // --- Validate Số tiền ---
        if ($data['amount'] <= 0) {
            $errors['amount'] = 'Số tiền thu phải lớn hơn 0.';
        }

        // --- Validate Mã chứng từ (Bắt buộc) ---
        if (empty($data['receipt_no'])) {
            $errors['receipt_no'] = 'Vui lòng nhập mã chứng từ hoặc số tham chiếu.';
        }

        // 3. Logic Validate Nghiệp vụ: Kiểm tra số tiền không vượt quá số nợ còn lại
        if (empty($errors['booking_id']) && empty($errors['amount'])) {
            $bookingModel = new Booking();
            $booking = $bookingModel->find($data['booking_id']);

            if (!$booking) {
                $errors['booking_id'] = 'Đơn hàng không tồn tại.';
            } else {
                $total  = (float)($booking['total_amount'] ?? 0); // Tổng tiền Booking
                $paid   = (float)($booking['paid_amount'] ?? 0); // Đã thanh toán
                $remain = $total - $paid; // Còn nợ

                // Kiểm tra nếu số tiền muốn thu lớn hơn số nợ + một ngưỡng sai số nhỏ (100 đ)
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
            // 5. Nếu không có lỗi => Lưu Phiếu thu vào DB
            (new Payment())->create([
                'booking_id' => $data['booking_id'],
                'amount'     => $data['amount'],
                'method'     => $data['method'],
                'receipt_no' => $data['receipt_no'],
                'note'       => $data['note'],
                'paid_at'    => date('Y-m-d H:i:s')
            ]);

            // 6. Tính toán lại tổng tiền đã trả (paid_amount) và cập nhật trạng thái Booking
            $this->recalcBooking($data['booking_id']);

            $_SESSION['flash_success'] = "Tạo phiếu thu thành công.";
            return $this->redirect(route('payment.index'));

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi hệ thống: " . $e->getMessage();
            return $this->redirect(route('payment.create'));
        }
    }

    /**
     * Hàm phụ trợ lấy danh sách booking còn nợ (total_amount > paid_amount).
     */
    private function getDebtBookings()
    {
        // Lấy tất cả Booking không bị Hủy
        $allBookings = (new Booking())->builder()
            ->where('state', '!=', 'CANCELLED')
            ->orderBy('id', 'DESC')
            ->get();

        $bookings = [];
        // Lọc ra các Booking mà số tiền đã thu (paid) nhỏ hơn tổng tiền (total)
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
     * Logic cập nhật tổng tiền đã thu (paid_amount) và trạng thái booking sau khi có thanh toán.
     */
    private function recalcBooking($bookingId) {
        // Tính tổng đã thu từ tất cả các phiếu thu của Booking này
        $paid = (new Payment())->builder()
            ->select('SUM(amount) as total')
            ->where('booking_id', $bookingId)
            ->first();
        $totalPaid = (float)($paid['total'] ?? 0);

        // Lấy tổng phải thu và trạng thái hiện tại của Booking
        $bk = (new Booking())->find($bookingId);
        $totalAmount = (float)$bk['total_amount'];

        // Cập nhật trạng thái
        $newState = $bk['state'];
        // Hoàn tất nếu đã trả đủ hoặc dư
        if ($totalPaid >= $totalAmount && $totalAmount > 0) $newState = 'COMPLETED';
        // Đặt cọc nếu đã trả một phần
        elseif ($totalPaid > 0) $newState = 'DEPOSITED';
        // Giữ nguyên (hoặc PLACED nếu totalPaid = 0)

        // Lưu paid_amount và state mới vào Booking
        (new Booking())->update($bookingId, [
            'paid_amount' => $totalPaid,
            'state' => $newState
        ]);
    }
}