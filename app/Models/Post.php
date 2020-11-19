<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $table = 'posts';

    public function user()
    {
        return $this->belongsTo('App\Models\User')->select('id', 'url', 'profile_photo_path', 'name');
    }
    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }
    public function likes()
    {
        return $this->hasMany('App\Models\Like');
    }
    public function images()
    {
        return $this->hasMany('App\Models\Image');
    }
}
