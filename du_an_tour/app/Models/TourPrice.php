<?php
namespace App\Models;

// 7) tour_price
class TourPrice extends BaseModel
{
    protected string $table = 'tour_price';
    
    // Lưu ý: Bảng này có ràng buộc kiểm tra Khoảng thời gian không chồng chéo.
}