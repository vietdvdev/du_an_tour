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

    // [GET] Form tạo phiếu thu (Độc lập)
    public function create(Request $req): Response
    {
        // Lấy danh sách Booking chưa thanh toán đủ (State != COMPLETED, CANCELLED)
        $bookings = (new Booking())->builder()
            ->where('state', '!=', 'CANCELLED')
            ->orderBy('id', 'DESC')
            ->get();

        return $this->render('payment/create', [
            'bookings' => $bookings
        ]);
    }

    // [POST] Lưu phiếu thu
    public function store(Request $req): Response
    {
        $bookingId = (int)$req->input('booking_id');
        $amount = (float)$req->input('amount');

        if ($bookingId <= 0 || $amount <= 0) {
            $_SESSION['flash_error'] = "Dữ liệu không hợp lệ.";
            return $this->redirect(route('payment.create'));
        }

        try {
            // 1. Tạo Payment
            (new Payment())->create([
                'booking_id' => $bookingId,
                'amount' => $amount,
                'method' => $req->input('method'),
                'receipt_no' => trim((string)$req->input('receipt_no')),
                'note' => trim((string)$req->input('note')),
                'paid_at' => date('Y-m-d H:i:s')
            ]);

            // 2. Tính lại tổng tiền booking (Logic Update Paid Amount)
            $this->recalcBooking($bookingId);

            $_SESSION['flash_success'] = "Tạo phiếu thu thành công.";
            return $this->redirect(route('payment.index'));

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi: " . $e->getMessage();
            return $this->redirect(route('payment.create'));
        }
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