<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\{
    HasOne,
    HasMany,
    BelongsToMany
};
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'role',
        'profile_image',
        'password',
        'otp_code',
        'otp_expires_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function savedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'saved_products')
            ->withTimestamps();
    }

    public function addresse(): HasOne
    {
        return $this->hasOne(Addresse::class);
    }


    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }


    //
    public function getProfileImageAttribute($value)
    {
        return $value ? Storage::url($value) : null;
    }

    public function getHashedPassword(): ?string
    {
        return $this->getAttributes()['password'] ?? null;
    }
}
