<?php
namespace App\Models;

class Assignment extends BaseModel
{
    protected string $table = 'assignment';

    /**
     * Lấy danh sách phân công theo Departure ID kèm tên HDV
     */
    public function getByDeparture(int $departureId): array
    {
        return $this->builder()
            ->select('assignment.*, users.full_name, users.phone')
            ->join('users', 'users.id', '=', 'assignment.guide_id')
            ->where('assignment.departure_id', $departureId)
            ->get();
    }
    
    /**
     * Kiểm tra trùng lặp (Một HDV không thể đi 2 tour cùng lúc)
     * Logic: (StartA <= EndB) and (EndA >= StartB)
     */
    public function checkOverlap(int $guideId, string $start, string $end): bool
    {
        $overlap = $this->builder()
            ->where('guide_id', $guideId)
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->first();
            
        return !empty($overlap);
    }
}