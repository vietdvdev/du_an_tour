<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Payment;

class BookingFinanceController extends BaseController
{
    // =========================================================================
    // QUẢN LÝ DỊCH VỤ (SERVICES)
    // =========================================================================

    public function addService(Request $req): Response
    {
        $bookingId = (int)($req->params['id'] ?? 0);
        if ($bookingId <= 0) return $this->redirect(route('booking.index'));

        $qty = (int)$req->input('qty');
        $price = (float)$req->input('unit_price');
        $cost = (float)$req->input('unit_cost');

        $data = [
            'booking_id'  => $bookingId,
            'type'        => $req->input('type'), // FLIGHT, HOTEL...
            'name'        => trim((string)$req->input('name')),
            'qty'         => $qty,
            'unit_price'  => $price,
            'amount'      => $qty * $price, // Tự tính thành tiền
            'unit_cost'   => $cost,
            'cost_amount' => $qty * $cost,  // Tự tính chi phí
            'status'      => 'REQUESTED'
        ];

        try {
            (new BookingService())->create($data);
            
            // [Logic Trigger] Cập nhật lại Total Amount của Booking
            // Tạm thời chỉ thông báo, hoặc bạn có thể viết hàm cộng dồn vào booking.total_amount
            // Nhưng chuẩn nhất là total_amount = (Giá tour * Pax) + Tổng Service.
            // Ở đây tôi giả định booking.total_amount đã bao gồm giá tour, giờ cộng thêm service.
            
            $this->updateBookingTotal($bookingId, $data['amount']);

            $_SESSION['flash_success'] = "Đã thêm dịch vụ thành công.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi: " . $e->getMessage();
        }

        return $this->redirect(route('booking.show', ['id' => $bookingId]));
    }

    public function deleteService(Request $req): Response
    {
        $bookingId = (int)($req->params['id'] ?? 0);
        $serviceId = (int)$req->input('service_id');

        if ($bookingId > 0 && $serviceId > 0) {
            $svc = (new BookingService())->find($serviceId);
            if ($svc) {
                (new BookingService())->delete($serviceId);
                // Trừ tiền khỏi booking
                $this->updateBookingTotal($bookingId, -($svc['amount']));
                $_SESSION['flash_success'] = "Đã xóa dịch vụ.";
            }
        }

        return $this->redirect(route('booking.show', ['id' => $bookingId]));
    }

    // =========================================================================
    // QUẢN LÝ THANH TOÁN (PAYMENTS)
    // =========================================================================

    public function addPayment(Request $req): Response
    {
        $bookingId = (int)($req->params['id'] ?? 0);
        if ($bookingId <= 0) return $this->redirect(route('booking.index'));

        $amount = (float)$req->input('amount');

        $data = [
            'booking_id' => $bookingId,
            'amount'     => $amount,
            'method'     => $req->input('method'),
            'receipt_no' => trim((string)$req->input('receipt_no')),
            'note'       => trim((string)$req->input('note')),
            'paid_at'    => date('Y-m-d H:i:s')
        ];

        try {
            (new Payment())->create($data);

            // [Logic Trigger] Cập nhật Paid Amount & Trạng thái Booking
            $this->recalcBookingPayment($bookingId);

            $_SESSION['flash_success'] = "Đã tạo phiếu thu thành công.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Lỗi: " . $e->getMessage();
        }

        return $this->redirect(route('booking.show', ['id' => $bookingId]));
    }

    public function deletePayment(Request $req): Response
    {
        $bookingId = (int)($req->params['id'] ?? 0);
        $paymentId = (int)$req->input('payment_id');

        if ($bookingId > 0 && $paymentId > 0) {
            (new Payment())->delete($paymentId);
            
            // [Logic Trigger] Tính lại
            $this->recalcBookingPayment($bookingId);
            
            $_SESSION['flash_success'] = "Đã hủy phiếu thu.";
        }

        return $this->redirect(route('booking.show', ['id' => $bookingId]));
    }

    // =========================================================================
    // CÁC HÀM HỖ TRỢ TÍNH TOÁN (PRIVATE)
    // =========================================================================

    /**
     * Cập nhật tổng tiền booking (Cộng/Trừ)
     */
    private function updateBookingTotal(int $bookingId, float $amountChange)
    {
        $bookingModel = new Booking();
        $booking = $bookingModel->find($bookingId);
        $newTotal = ($booking['total_amount'] ?? 0) + $amountChange;
        
        $bookingModel->update($bookingId, ['total_amount' => $newTotal]);
    }

    /**
     * Tính tổng tiền đã trả và cập nhật trạng thái
     */
    private function recalcBookingPayment(int $bookingId)
    {
        $paidResult = (new Payment())->builder()
            ->select('SUM(amount) as total')
            ->where('booking_id', $bookingId)
            ->first();
            
        $totalPaid = (float)($paidResult['total'] ?? 0);
        
        $booking = (new Booking())->find($bookingId);
        $totalAmount = (float)$booking['total_amount'];
        
        $newState = $booking['state'];
        // Logic chuyển trạng thái tự động
        if ($totalPaid >= $totalAmount && $totalAmount > 0) {
            $newState = 'COMPLETED'; // Đã thanh toán đủ
        } elseif ($totalPaid > 0) {
            $newState = 'DEPOSITED'; // Đã cọc
        } else {
            $newState = 'PLACED'; // Chưa thanh toán
        }

        (new Booking())->update($bookingId, [
            'paid_amount' => $totalPaid,
            'state'       => $newState
        ]);
    }
}