<?php
namespace App\Models;

// 6) tour_image
class TourImage extends BaseModel
{
    protected string $table = 'tour_image';

    /**
     * Phương thức tùy chỉnh để kiểm tra xem Tour có ảnh bìa hay không
     * (Cần triển khai logic Query Builder cụ thể)
     */
    public function hasCoverImage(int $tourId): bool
    {
        // Giả lập logic: return $this->builder()->where('tour_id', $tourId)->where('is_cover', 1)->exists();
        return true; 
    }
}