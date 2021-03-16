<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class Post extends Model implements Searchable
{
    use HasFactory;
    protected $table = 'posts';

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
        return $this->belongsTo('App\Models\User')->select('id', 'url', 'profile_photo_path', 'name');
    }
    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }
    public function likes()
    {
        return $this->morphMany('App\Models\Like', 'likeable')->orderBy('created_at', 'desc');
    }
    public function liked()
    {
        return $this->morphMany('App\Models\Like', 'likeable')->where('status', '>', 0)->orderBy('created_at', 'desc');
    }
    public function images()
    {
        return $this->morphMany('App\Models\Like', 'imageable');
    }

    public function likeStatus()
    {
        return $this->morphOne('App\Models\Like', 'likeable')->where('user_id', auth()->user()->id);
    }
}
