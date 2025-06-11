<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedProduct extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $primaryKey = ['product_id', 'user_id'];

    protected $fillable = [
        'product_id',
        'user_id'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

   
    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Business logic
    public static function isSaved(int $productId, int $userId): bool
    {
        return self::where('product_id', $productId)
            ->where('user_id', $userId)
            ->exists();
    }

    public static function toggleSave(int $productId, int $userId): void
    {
        $existing = self::where('product_id', $productId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            self::create([
                'product_id' => $productId,
                'user_id' => $userId
            ]);
        }
    }

    public static function countSaves(int $productId): int
    {
        return self::where('product_id', $productId)->count();
    }
}