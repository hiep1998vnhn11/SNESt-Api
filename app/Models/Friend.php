<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    use HasFactory;
    protected $table = 'friends';
    public function user()
    {
        return $this->belongsTo('App\Models\User')->select(['name', 'profile_photo_path', 'url']);
    }

    public function user_friend()
    {
        return $this->belongsTo('App\Models\User', 'friend_id');
    }
}
