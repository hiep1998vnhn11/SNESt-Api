<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'type',
        'size',
        'extension',
        'mime_type',
        'user_id',
        'thumbnail_id',
        'object_id',
        'object_type',
    ];
}
