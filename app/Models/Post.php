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
        return $this->morphMany('App\Models\Comment', 'liketable');
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
