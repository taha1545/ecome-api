<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cupon extends Model
{
    use HasFactory;

    protected $table = 'coupons';

    protected $fillable = [
        'code',
        'value',
        'max_usage',
        'used_count',
        'expires_at',
        'is_active'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'max_usage' => 'integer',
        'used_count' => 'integer'
    ];


    //relation
    public function Order()
    {
        return $this->hasMany(Order::class);
    }
}
