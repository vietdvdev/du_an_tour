<?php
namespace App\Models;

// 11) booking
class Booking extends BaseModel
{
    protected string $table = 'booking';
    
    // Lưu ý: Có ràng buộc UNIQUE(code).
}
