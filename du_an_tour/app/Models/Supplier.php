<?php
namespace App\Models;

class Supplier extends BaseModel
{
    protected string $table = 'supplier';

    protected string $primaryKey = 'id';
     protected $fillable = [
        'name',
        'type',
        'contact',
        'phone',
        'address',
        'note ',
    ];


}
