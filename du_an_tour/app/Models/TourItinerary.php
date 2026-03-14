<?php
namespace App\Models;

class TourItinerary extends BaseModel
{
    protected string $table = 'tour_itinerary';
    
    // Lưu ý: Bảng này có khóa UNIQUE là (tour_id, day_no)
    // Các phương thức cơ bản (find, update, delete) chỉ dùng id.
}