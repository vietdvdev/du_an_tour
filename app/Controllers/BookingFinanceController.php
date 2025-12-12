<?php
namespace App\Controllers;

// Import các lớp cần thiết
use App\Core\Request;
use App\Core\Response;
use App\Models\Booking; // Booking Model (Đơn đặt chỗ)
use App\Models\BookingService; // BookingService Model (Dịch vụ bổ sung)
use App\Models\Payment; // Payment Model (Phiếu thu)

/**
 * Class BookingFinanceController
 * Xử lý các nghiệp vụ tài chính của Booking: Dịch vụ bổ sung và Thanh toán.
 */
class BookingFinanceController extends BaseController
{
    // =========================================================================
    // QUẢN LÝ DỊCH VỤ (SERVICES)
    // =========================================================================

    /**
     * [POST] Thêm một dịch vụ bổ sung vào Booking.
     */
    public function addService(Request $req): Response
    {
        $bookingId = (int)($req->params['id'] ?? 0);
        // Kiểm tra bookingId hợp lệ
        if ($bookingId <= 0) return $this->redirect(route('booking.index'));

        // Lấy dữ liệu input
        $qty = (int)$req->input('qty');
        $price = (float)$req->input('unit_price'); // Giá bán (Price)
        $cost = (float)$req->input('unit_cost'); // Giá vốn (Cost)

        $data = [
            'booking_id'    => $bookingId,
            'type'          => $req->input('type'), // Loại dịch vụ (Ví dụ: FLIGHT, HOTEL...)
            'name'          => trim((string)$req->input('name')), // Tên dịch vụ
            'qty'           => $qty,
            'unit_price'    => $price,
            'amount'        => $qty * $price, // Tổng thành tiền (Giá bán * Số lượng)
            'unit_cost'     => $cost,
            'cost_amount'   => $qty * $cost,  // Tổng chi phí (Giá vốn * Số lượng)
            'status'        => 'REQUESTED' // Trạng thái ban đầu
        ];

        try {
            // Lưu dịch vụ mới vào CSDL
            (new BookingService())->create($data);
            
            // Cập nhật lại Total Amount của Booking (Cộng thêm giá bán của dịch vụ vừa thêm)
            $this->updateBookingTotal($bookingId, $data['amount']);

            $_SESSION['flash_success'] = "Đã thêm dịch vụ thành công.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi: " . $e->getMessage();
        }

        // Chuyển hướng về trang chi tiết Booking
        return $this->redirect(route('booking.show', ['id' => $bookingId]));
    }

    /**
     * [POST] Xóa một dịch vụ bổ sung khỏi Booking.
     */
    public function deleteService(Request $req): Response
    {
        $bookingId = (int)($req->params['id'] ?? 0);
        $serviceId = (int)$req->input('service_id');

        if ($bookingId > 0 && $serviceId > 0) {
            $svc = (new BookingService())->find($serviceId);
            if ($svc) {
                // Xóa bản ghi dịch vụ
                (new BookingService())->delete($serviceId);
                // Trừ tiền khỏi tổng tiền Booking (giá trị âm)
                $this->updateBookingTotal($bookingId, -($svc['amount']));
                $_SESSION['flash_success'] = "Đã xóa dịch vụ.";
            }
        }

        return $this->redirect(route('booking.show', ['id' => $bookingId]));
    }

    // =========================================================================
    // QUẢN LÝ THANH TOÁN (PAYMENTS)
    // =========================================================================

    /**
     * [POST] Thêm một phiếu thu (ghi nhận thanh toán) vào Booking.
     */
    public function addPayment(Request $req): Response
    {
        $bookingId = (int)($req->params['id'] ?? 0);
        if ($bookingId <= 0) return $this->redirect(route('booking.index'));

        $amount = (float)$req->input('amount'); // Số tiền thanh toán

        $data = [
            'booking_id' => $bookingId,
            'amount'     => $amount,
            'method'     => $req->input('method'), // Phương thức thanh toán
            'receipt_no' => trim((string)$req->input('receipt_no')), // Số biên lai/hóa đơn
            'note'       => trim((string)$req->input('note')),
            'paid_at'    => date('Y-m-d H:i:s') // Thời điểm thanh toán
        ];

        try {
            // Lưu bản ghi thanh toán
            (new Payment())->create($data);

            // Tính lại tổng tiền đã trả (paid_amount) và cập nhật trạng thái Booking
            $this->recalcBookingPayment($bookingId);

            $_SESSION['flash_success'] = "Đã tạo phiếu thu thành công.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi: " . $e->getMessage();
        }

        return $this->redirect(route('booking.show', ['id' => $bookingId]));
    }

    /**
     * [POST] Xóa một phiếu thu khỏi Booking.
     */
    public function deletePayment(Request $req): Response
    {
        $bookingId = (int)($req->params['id'] ?? 0);
        $paymentId = (int)$req->input('payment_id');

        if ($bookingId > 0 && $paymentId > 0) {
            (new Payment())->delete($paymentId);
            
            // Sau khi xóa, tính toán lại tổng tiền đã trả và trạng thái
            $this->recalcBookingPayment($bookingId);
            
            $_SESSION['flash_success'] = "Đã hủy phiếu thu.";
        }

        return $this->redirect(route('booking.show', ['id' => $bookingId]));
    }

    // =========================================================================
    // CÁC HÀM HỖ TRỢ TÍNH TOÁN (PRIVATE)
    // =========================================================================

    /**
     * Cập nhật tổng tiền booking (total_amount) (Cộng/Trừ)
     * Được gọi khi thêm/xóa BookingService.
     */
    private function updateBookingTotal(int $bookingId, float $amountChange)
    {
        $bookingModel = new Booking();
        $booking = $bookingModel->find($bookingId);
        // Tính tổng tiền mới
        $newTotal = ($booking['total_amount'] ?? 0) + $amountChange;
        
        // Cập nhật vào CSDL
        $bookingModel->update($bookingId, ['total_amount' => $newTotal]);
    }

    /**
     * Tính tổng tiền đã trả (paid_amount) và cập nhật trạng thái Booking.
     * Được gọi khi thêm/xóa Payment.
     */
    private function recalcBookingPayment(int $bookingId)
    {
        // 1. Tính tổng tiền đã thanh toán từ bảng Payment
        $paidResult = (new Payment())->builder()
            ->select('SUM(amount) as total')
            ->where('booking_id', $bookingId)
            ->first();
            
        $totalPaid = (float)($paidResult['total'] ?? 0);
        
        // 2. Lấy thông tin Booking hiện tại để so sánh
        $booking = (new Booking())->find($bookingId);
        $totalAmount = (float)$booking['total_amount'];
        
        $newState = $booking['state']; // Giữ trạng thái cũ (nếu không đổi)

        // 3. Logic chuyển trạng thái tự động
        if ($totalPaid >= $totalAmount && $totalAmount > 0) {
            $newState = 'COMPLETED'; // Đã thanh toán đủ (Hoàn tất)
        } elseif ($totalPaid > 0) {
            $newState = 'DEPOSITED'; // Đã cọc (Có thanh toán nhưng chưa đủ)
        } else {
            $newState = 'PLACED'; // Chưa thanh toán (Mới đặt)
        }

        // 4. Cập nhật paid_amount và state vào Booking
        (new Booking())->update($bookingId, [
            'paid_amount' => $totalPaid,
            'state'       => $newState
        ]);
    }
}