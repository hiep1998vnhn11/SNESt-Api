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
        return $this->belongsTo('App\Models\User');
    }

    public function user_friend()
    {
        return $this->belongsTo('App\Models\User', 'friend_id');
    }
}
