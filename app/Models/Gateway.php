<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    use HasFactory;
    protected $fillable = [
        'service_path',
        'api_key',
        'description'
    ];

    public function transactions(): HasMany {
        return $this->hasMany(Transaction::class);
    }
}
