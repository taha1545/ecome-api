<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pending' => 'Pending',
        'succeeded' => 'Succeeded',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
        'requires_action' => 'Requires Action'
    ];

    protected $fillable = [
        'order_id',
        'user_id',
        'method',
        'amount',
        'currency',
        'status',
        'gateway_id',
        'gateway_response',
        'error_code',
        'error_message',
        'processed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}