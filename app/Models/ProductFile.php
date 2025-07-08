<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductFile extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'path',
        'type',
        'product_id'
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
       public function getPathAttribute($value)
    {
        return $value ? Storage::url($value) : null;
    }

 
}