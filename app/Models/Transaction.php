<?php

namespace App\Models;

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
        'is_verified'
    ];

    public static function isVerified($transactionId): bool {
        return static::where('is_verified', 1)->exists($transactionId);
    }
}
