<?php
namespace App\Models;

// 4) tour_policy
class TourPolicy extends BaseModel
{
    protected string $table = 'tour_policy';
    protected string $primaryKey = 'tour_id';
    
    // Lưu ý: tour_id là khóa chính.
}