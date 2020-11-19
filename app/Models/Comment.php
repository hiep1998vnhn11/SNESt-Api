<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $table = 'comments';
    public function post()
    {
        return $this->belongsTo('App\Models\Post');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User')->select('id', 'url', 'profile_photo_path', 'name');
    }
    public function sub_comments()
    {
        return $this->hasMany('App\Models\SubComment');
    }
}
