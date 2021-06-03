<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    use HasFactory;
    protected $table = 'friends';

    protected $fillable = [
        'user_id', 'friend_id', 'status'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User')->select(['id', 'name', 'profile_photo_path', 'url']);
    }

    public function user_friend()
    {
        return $this->belongsTo('App\Models\User', 'friend_id')->select(['id', 'full_name', 'profile_photo_path', 'url']);
    }
}
