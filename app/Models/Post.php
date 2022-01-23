<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Post extends Model implements Searchable
{
    use HasFactory;
    protected $table = 'posts';

    protected $appends = [
        'like_group'
    ];

    public function getSearchResult(): SearchResult
    {
        return new SearchResult(
            $this,
            $this->content,
            'post'
        );
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User')->select('id', 'url', 'profile_photo_path', 'full_name');
    }
    public function comments()
    {
        return $this->hasMany('App\Models\Comment', 'post_id', 'posts.id');
    }
    public function likes()
    {
        return $this->hasMany('App\Models\Like', 'likeable_id')
            ->where('likeable_type', 'post');
    }
    public function liked()
    {
        return $this->morphMany('App\Models\Like', 'likeable')->where('likes.status', '>', 0)->orderBy('likes.created_at', 'desc');
    }
    public function images()
    {
        return $this->morphMany('App\Models\Image', 'imageable');
    }

    public function likeStatus()
    {
        return $this->hasOne('App\Models\Like', 'likeable_id')
            ->where('likeable_type', 'post')
            ->where('user_id', auth()->user()->id);
    }

    public function getContentAttribute($value)
    {
        return nl2br($value);
    }

    public function getLikeGroupAttribute()
    {
        return Like::query()
            ->where('likeable_type', 'post')
            ->where('likeable_id', $this->id)
            ->where('status', '>', 0)
            ->select('status', DB::raw('COUNT(*) as counter'))
            ->groupBy('status')
            ->orderBy('counter', 'DESC')
            ->take(3)
            ->get();
    }

    public function groupAndCountStatus()
    {
        return Like::query()
            ->where('likeable_type', 'post')
            ->where('likeable_id', $this->id)
            ->where('status', '>', 0)
            ->select('status', DB::raw('COUNT(*) as counter'))
            ->groupBy('status')
            ->get();
    }

    public function media()
    {
        return $this->hasMany('App\Models\Media', 'object_id')
            ->select(['object_id', 'url', 'type'])
            ->where('object_type', 'post');
    }
}
