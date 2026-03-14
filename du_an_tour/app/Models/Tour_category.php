<?php
namespace App\Models;

class Tour_category extends BaseModel
{
    protected string $table = 'tour_category';

    protected string $primaryKey = 'id';
     protected $fillable = [
        'name',
        'description',
        'is_active',
    ];
}
