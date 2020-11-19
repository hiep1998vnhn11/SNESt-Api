<?php

namespace App\Http\Services;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Arr;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;

class PostService
{
    public function getPostForAuth($param)
    {
        $limit = Arr::get($param, 'limit', config('const.DEFAULT_PER_PAGE'));
        $userUrl = Arr::get($param, 'user_url', null);
        $searchKey = Arr::get($param, 'search_key', null);
        $type = Arr::get($param, 'type', null);
        if ($userUrl) { //Get user by user url (not require auth)
            $user = User::where('url', $userUrl)->first();
            $posts = $user->posts()->orderBy('created_at', 'desc')->paginate($limit);
            foreach ($posts as $post) {
                $post->user;
                $post->loadCount(['likes' => function ($query) {
                    $query->where('status', 1);
                }]);
                $likes = $post->likes->where('status', 1);
                foreach ($likes as $like) {
                    $like->user;
                    if (auth()->user() && $like->user_id == auth()->user()->id) {
                        $post->isLiked = true;
                    }
                }
                if (auth()->user() && !$post->isLiked) $post->isLiked = false;
                $post->loadCount('comments');
                $post->images;
            }
            return $posts;
        } else if ($type) {
            switch ($type) {
                case 'feed':
                    return $this->getPostForFeed($limit, $searchKey);
                default:
                    return null;
            }
        } else {
            return null;
        }
    }

    public function getPostForFeed($limit, $searchKey) //friend only!!!!
    {
        $user = auth()->user();
        $users = [$user->id];
        $friends = $user->friends()->select('friend_id')->where('status', 1)->where('blocked', 0)->get();
        foreach ($friends as $friend) {
            $friend_id = $friend->friend_id;
            if ($user->id != $friend_id)
                array_push($users, $friend_id);
        }
        $posts = Post::whereYear('created_at', '=', Carbon::now()->year)
            ->whereIn('user_id', $users)
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
        foreach ($posts as $post) {
            $post->user;
            $post->loadCount(['likes' => function ($query) {
                $query->where('status', 1);
            }]);
            $likes = $post->likes->where('status', 1);
            foreach ($likes as $like) {
                $like->user;
                if (auth()->user() && $like->user_id == auth()->user()->id) {
                    $post->isLiked = true;
                }
            }
            if (auth()->user() && !$post->isLiked) $post->isLiked = false;
            $post->loadCount('comments');
            $post->images;
        }
        return $posts;
    }
}
