<?php
namespace App\Models;

// 10) departure
class Departure extends BaseModel
{
    protected string $table = 'departure';
    
    // Lưu ý: Có ràng buộc UNIQUE(tour_id, start_date) và capacity.
}