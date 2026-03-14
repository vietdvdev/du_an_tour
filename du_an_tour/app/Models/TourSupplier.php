<?php
namespace App\Models;

// 9) tour_supplier (Bảng N-N)
class TourSupplier extends BaseModel
{
    protected string $table = 'tour_supplier';
    
    // Lưu ý: Khóa chính là tổ hợp (tour_id, supplier_id). 
    // Các thao tác find/update/delete cần được viết thủ công nếu dùng 2 khóa.
}