<?php
namespace App\Models;

// 19) traveler_special
class TravelerSpecial extends BaseModel
{
    protected string $table = 'traveler_special';
    
    // Lưu ý: Có ràng buộc UNIQUE(traveler_id, type, details) WHERE active IS TRUE.
}