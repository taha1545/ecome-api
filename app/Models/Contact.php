<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'user_id',
        'phone',
        'notes',
        'type',
        'is_primary'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public const TYPES = [
        'personal' => 'Personal',
        'emergency' => 'Emergency',
        'business' => 'Business',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
