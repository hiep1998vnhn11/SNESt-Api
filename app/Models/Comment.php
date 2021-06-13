<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Comment extends Model
{
    use HasFactory;
    protected $table = 'comments';
    protected $appends = [
        'like_group'
    ];
    public function post()
    {
        return $this->belongsTo('App\Models\Post');
    }

    public function likes()
    {
        return $this->morphMany('App\Models\Like', 'likeable')->orderBy('created_at', 'desc');
    }

    public function liked()
    {
        return $this->morphMany('App\Models\Like', 'likeable')->orderBy('created_at', 'desc')->where('status', '>', 0);
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
    public function sub_comments()
    {
        return $this->hasMany('App\Models\SubComment');
    }
    public function likeStatus()
    {
        return $this->morphOne('App\Models\Like', 'likeable')->where('user_id', auth()->user()->id);
    }

    public function images()
    {
        return $this->morphMany('App\Models\Like', 'imageable');
    }

    public function groupAndCountStatus()
    {
        return Like::query()
            ->where('likeable_type', 'App\Models\Comment')
            ->where('likeable_id', $this->id)
            ->where('status', '>', 0)
            ->select('status', DB::raw('COUNT(*) as counter'))
            ->groupBy('status')
            ->get();
    }

    public function getLikeGroupAttribute()
    {
        return Like::query()
            ->where('likeable_type', 'App\Models\Comment')
            ->where('likeable_id', $this->id)
            ->where('status', '>', 0)
            ->select('status', DB::raw('COUNT(*) as counter'))
            ->groupBy('status')
            ->get();
    }
}
