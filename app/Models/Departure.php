<?php
namespace App\Models;

class Departure extends BaseModel
{
    protected string $table = 'departure';

    /**
     * Lấy danh sách đợt khởi hành kèm thông tin Tour và Số chỗ đã đặt
     * Logic: Chỗ đã đặt = Tổng pax_count của các booking không bị Hủy
     */
    public function getAllWithStats()
    {
        // Lưu ý: Cú pháp SQL này tương thích cả MySQL và PostgreSQL
        return $this->builder()
            ->select('
                departure.*, 
                tour.code as tour_code, 
                tour.name as tour_name,
                COALESCE(SUM(CASE WHEN booking.state != \'CANCELLED\' THEN booking.pax_count ELSE 0 END), 0) as booked_seats
            ')
            ->join('tour', 'tour.id', '=', 'departure.tour_id')
            ->leftJoin('booking', 'booking.departure_id', '=', 'departure.id')
            ->groupBy('departure.id', 'tour.code', 'tour.name') // Group by các cột không tổng hợp
            ->orderBy('departure.start_date', 'DESC')
            ->get();
    }

    /**
     * Lấy số lượng ghế đã bán thực tế của 1 đợt (để check rule khi update)
     */
    public function getSoldSeats(int $departureId): int
    {
        $result = $this->builder()
            ->select('COALESCE(SUM(pax_count), 0) as total')
            ->from('booking') // Query trực tiếp bảng booking
            ->where('departure_id', $departureId)
            ->where('state', '!=', 'CANCELLED') // Không tính đơn hủy
            ->first();

        return (int)($result['total'] ?? 0);
    }
}