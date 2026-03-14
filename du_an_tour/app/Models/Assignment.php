<?php
namespace App\Models;

// 15) assignment
class Assignment extends BaseModel
{
    protected string $table = 'assignment';
    
    // Lưu ý: Có ràng buộc UNIQUE(departure_id, guide_id) để tránh trùng lịch.
}