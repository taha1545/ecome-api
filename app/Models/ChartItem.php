<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'chart_id',
        'label',
        'value',
        'position'
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'position' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function chart(): BelongsTo
    {
        return $this->belongsTo(Chart::class);
    }

    
}