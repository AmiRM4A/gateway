<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'api_key',
        'description'
    ];

    public static function generateUniqueId(): string {
        return md5(uniqid('', true));
    }
}
