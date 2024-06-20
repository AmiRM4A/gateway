<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'transaction_id',
        'amount',
        'link',
        'is_verified',
        'unique_id'
    ];

    public static function isVerified($uniqueId): bool {
        return static::where('is_verified', 1)->exists($uniqueId);
    }

    public static function generateUniqueId(): string {
        return hash('sha256', Str::random(32) . uniqid('', true));
    }
}
