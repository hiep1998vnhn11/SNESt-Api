<?php

namespace App\Http\Services;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Arr;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Follow;
use App\Models\Friend;
use App\Models\Like;

class FollowService
{
    /**
     * Create comment on a post.
     *
     * @param  Request $request->all(), 
     * 
     * @param user_url 
     * 
     * @param relationship
     *
     * @return data or false
     */
    public function search($params = [])
    {
        $searchKey = Arr::get($params, 'search_key', null);
        $limit = Arr::get($params, 'limit', config('const.DEFAULT_PER_PAGE'));
        $userId = auth()->user()->id;
        $query = Follow::query()
            ->where('follows.user_id', $userId)
            ->where('follows.status', 1)
            ->join('users', 'follows.followed_id', 'users.id')
            ->select(
                'users.full_name',
                'users.phone_number',
                'users.url',
                'users.profile_photo_path',
                'follows.id'
            );
        if ($searchKey) {
            $query = $query->where(function ($q) use ($searchKey) {
                $q->where('users.full_name', 'like', '%' . $searchKey . '%')
                    ->orWhere('users.url', 'like', '%' . $searchKey . '%')
                    ->orWhere('users.phone_number', 'like', '%' . $searchKey . '%')
                    ->orWhere('users.slug', 'like', '%' . $searchKey . '%');
            });
        }
        $query = $query->orderBy('follows.updated_at', 'desc')->paginate($limit);
        return $query;
    }

    public function follow($userId)
    {
        $follow = Follow::withTrashed()
            ->where('followed_id', $userId)
            ->first();
        if ($follow) {
            $follow->restore();
            return $follow;
        }
        $follow = Follow::create([
            'user_id' => auth()->user()->id,
            'followed_id' => $userId
        ]);
        return $follow;
    }

    public function unFollow($userId)
    {
        $follow = auth()->user()->follows()
            ->where('followed_id', $userId)
            ->firstOrFail();
        $follow->delete();
        return $follow;
    }
}
