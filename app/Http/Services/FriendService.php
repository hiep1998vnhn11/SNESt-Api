<?php

namespace App\Http\Services;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Arr;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Friend;
use App\Models\Like;

class FriendService
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
    public function handleFriend($param)
    {
        $user_url = Arr::get($param, 'user_url', null);
        if ($user_url == auth()->user()->url) return false;
        $relationship = Arr::get($param, 'relationship', null);

        $user = User::select('id', 'name')
            ->where('url', $user_url)
            ->first();
        if (!$user) return false;

        $isFriend = auth()->user()->friends
            ->where('friend_id', $user->id)
            ->first();  // Check if user are create a friend field ?
        switch ($relationship) {
            case 'blocked':
                if (!$isFriend) {
                    $friend = new Friend();
                    $friend->blocked = 1;
                    $friend->user_id = auth()->user()->id;
                    $friend->friend_id = $user->id;
                    $friend->save();
                    return $friend;
                } else if ($isFriend->blocked)
                    return false;
                else {   // Friend and not blocked => blocked!!!!
                    $isFriend->blocked = 1;
                    $isFriend->save();
                    return $isFriend;
                }
                break;
            case 'unblocked':
                if (!$isFriend) return false;
                else if (!$isFriend->blocked) return false;
                else {   // Friend and not blocked => blocked!!!!
                    $isFriend->blocked = 0;
                    $isFriend->save();
                    return $isFriend;
                }
                break;
            case 'friend':
                if (!$isFriend) {
                    $friend = new Friend();
                    $friend->status = 1;
                    $friend->blocked = 0;
                    $friend->user_id = auth()->user()->id;
                    $friend->friend_id = $user->id;
                    $friend->save();
                    return $friend;
                } else if ($isFriend->blocked) {
                    $isFriend->blocked = 0;
                    $isFriend->save();
                    return $isFriend;
                } else if (!$isFriend->status) {
                    $isFriend->status = 1;
                    $isFriend->save();
                    return $isFriend;
                } else return false;
                break;
            case 'unfriend':
                if (!$isFriend) return false;
                else if ($isFriend->status) {
                    $isFriend->status = 0;
                    $isFriend->save();
                    return $isFriend;
                } else return false;
                break;
            default:
                return false;
        }
    }
}
