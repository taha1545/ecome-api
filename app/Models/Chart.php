<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chart extends Model
{
    use HasFactory;

    public const TYPES = [
        'bar' => 'Bar Chart',
        'line' => 'Line Chart',
        'pie' => 'Pie Chart',
        'doughnut' => 'Doughnut Chart',
        'radar' => 'Radar Chart',
        'polarArea' => 'Polar Area Chart'
    ];

    protected $fillable = [
        'name',
        'type',
        'description'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function items(): HasMany
    {
        return $this->hasMany(ChartItem::class)->orderBy('position');
    }

   
}