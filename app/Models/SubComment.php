<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubComment extends Model
{
    use HasFactory;
    protected $table = 'sub_comments';

    public function comment()
    {
        return $this->belongsTo('App\Models\Comment');
    }

    public function likes()
    {
        return $this->morphMany('App\Models\Like', 'likeable')->orderBy('created_at', 'desc');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\user');
    }

    public function likeStatus()
    {
        return $this->morphOne('App\Models\Like', 'likeable')->where('user_id', auth()->user()->id);
    }
}
