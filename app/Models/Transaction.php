<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    public function getRouteKeyName(): string {
        return 'unique_id';
    }

    public function gateway(): BelongsTo {
        return $this->belongsTo(Gateway::class);
    }

    public static function isVerified($uniqueId): bool {
        return static::where('is_verified', 1)->exists($uniqueId);
    }

    public static function generateUniqueId(): string {
        return md5(uniqid('', true));
    }
}
