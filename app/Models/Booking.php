<?php
namespace App\Models;


class Booking extends BaseModel
{
    protected string $table = 'booking';

    /**
     * Sinh mã booking ngẫu nhiên duy nhất
     * Format: BK-YYYYMMDD-XXXX (VD: BK-20231126-A1B2)
     */
    public static function generateCode(): string
        {
            return 'BK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        }
        
    /**
     * Lấy danh sách booking kèm thông tin Tour và Departure
     */
    public function getAllWithDetails()
    {
        return $this->builder()
            ->select('booking.*, departure.start_date, departure.end_date, tour.code as tour_code, tour.name as tour_name')
            ->join('departure', 'departure.id', '=', 'booking.departure_id')
            ->join('tour', 'tour.id', '=', 'departure.tour_id')
            ->orderBy('booking.id', 'DESC')
            ->get();
    }
}