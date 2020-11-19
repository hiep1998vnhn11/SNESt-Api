<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    protected $table = 'rooms';

    public function user()
    {
        return $this->belongsTo('App\Models\User')->select('id', 'url', 'profile_photo_path', 'name');
    }

    public function user_with()
    {
        return $this->belongsTo('App\Models\User', 'with_id')->select('id', 'url', 'profile_photo_path', 'name');
    }

    public function messages()
    {
        return $this->hasMany('App\Models\Message');
    }
}
