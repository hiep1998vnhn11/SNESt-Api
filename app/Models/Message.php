<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $table = 'messages';

    public function user()
    {
        return $this->belongsTo('App\Models\User')->select('id', 'url', 'profile_photo_path', 'name');
    }

    public function room()
    {
        return $this->belongsTo('App\Models\Room');
    }

}
