<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Friend;

class GuestController extends Controller
{
    public function getUser(String  $url)
    {
        $user = User::where('url', $url)
            ->leftJoin('infos', 'users.id', 'infos.user_id')
            ->select(
                'users.*',
                'infos.gender',
                'infos.profile_background_path',
                'infos.birthday',
                'infos.live_at',
                'infos.from',
                'infos.link_to_social',
                'infos.story',
                'infos.story_privacy',
                'infos.locale',
            )
            ->first();
        if (!$user) return $this->sendRespondError();
        $user->loadCount(['friends', 'follows', 'followeds']);
        $friends = Friend::where('user_id', $user->id)
            ->where('status', 1)
            ->leftJoin('users', 'users.id', 'friends.friend_id')
            ->select(
                'friends.*',
                'users.full_name',
                'users.profile_photo_path',
                'users.url',
            )
            ->orderBy('friends.updated_at', 'desc')
            ->limit(config('const.DEFAULT_PER_PAGE'))
            ->get();
        if (auth()->user()) {
            $friendStatus = auth()->user()->friends()
                ->where('friend_id', $user->id)
                ->first();
            $followStatus = auth()->user()->follows()
                ->where('followed_id', $user->id)
                ->first();
            return $this->sendRespondSuccess([
                'user' => $user,
                'friends' => $friends,
                'friend_status' => $friendStatus,
                'follow_status' => $followStatus
            ]);
        }
        return $this->sendRespondSuccess([
            'user' => $user,
            'friends' => $friends,
        ]);
    }
}
