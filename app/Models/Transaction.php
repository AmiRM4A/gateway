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

    protected $casts = [
        'order_id' => 'string',
        'gateway_id' => 'int',
        'status_code' => 'int'
    ];

    public function getRouteKeyName(): string {
        return 'unique_id';
    }

    public function gateway(): BelongsTo {
        return $this->belongsTo(Gateway::class);
    }

    public static function generateUniqueId(): string {
        return md5(uniqid('', true));
    }
}
