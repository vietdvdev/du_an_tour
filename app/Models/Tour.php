<?php
namespace App\Models;

class Tour extends BaseModel
{
    protected string $table = 'tour';

    protected string $primaryKey = 'id';
     protected $fillable = [
        'code',
        'name',
        'category_id',
        'description',
        'state',
        'is_active',
        'created_at',
        'updated_at',
    ];
}
