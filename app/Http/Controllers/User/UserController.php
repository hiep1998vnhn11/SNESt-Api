<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\GuestUserRequest;
use App\Http\Requests\ImageRequest;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ChangeInfoRequest;
use App\Http\Requests\CheckUrlRequest;
use Illuminate\Support\Arr;

class UserController extends Controller
{

    /**
     * Get a Post.
     *
     * @param Post
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getForGuest(GuestUserRequest $request)
    {
        $user_url = $request->user_url;
        if (Str::contains($user_url, 'admin'))
            return $this->sendRespondError(
                null,
                'Not found User!',
                config('const.STATUS_CODE_BAD_REQUEST')
            );
        $user = $this->findUser($user_url);
        $friends = $user->friends()
            ->select('friend_id')
            ->where('status', 1)
            ->where('blocked', 0)
            ->get();
        foreach ($friends as $friend) {
            $friend->user_friend;
        }
        $user->friends = $friends;
        $user->friends_count = count($friends);
        $info = $user->info;
        $info->jobs;
        $info->educates;
        if (!$user) return $this->sendRespondError(
            null,
            'Not found User!',
            config('const.STATUS_CODE_BAD_REQUEST')
        );
        return $this->sendRespondSuccess($user, 'Get User successfully!');
    }

    public function getForAuth(GuestUserRequest $request)
    {
        $user_url = $request->user_url;
        if (Str::contains($user_url, 'admin'))
            return $this->sendRespondError(
                null,
                'Not found User!',
                config('const.STATUS_CODE_BAD_REQUEST')
            );
        if ($user_url == auth()->user()->url) $user = auth()->user();
        else $user = $this->findUser($user_url);
        $user->onlineStatus = $user->isOnline();
        $friends = $user->friends()
            ->select('friend_id', 'id')
            ->where('status', 1)
            ->where('blocked', 0)
            ->get();
        if ($user_url == auth()->user()->url) {
            $user->friend_status = config('const.FRIEND_STATUS_NONE');
            foreach ($friends as $friend) { // Friend of this user url
                $friend->user_friend;
            }
        } else {
            $friendOnYourSide = false;
            $friendOnOtherSide = false;
            $friend_status_id = null;
            foreach ($friends as $friend) { // Friend of this user url
                $friend->user_friend;
                if ($friend->friend_id == auth()->user()->id) {
                    $friendOnOtherSide = true;
                    $friend_status_id = $friend->id;
                }
            }
            $yourFriend = auth()->user()
                ->friends()
                ->where('status', 1)
                ->where('blocked', 0)
                ->where('friend_id', $user->id)
                ->first();
            if ($yourFriend) $friendOnYourSide = true;
            $status = config('const.FRIEND_STATUS_NONE');
            if ($friendOnOtherSide && $friendOnYourSide) $status = config('const.FRIEND_STATUS_FRIEND');
            else if ($friendOnOtherSide && !$friendOnYourSide) {
                $status = config('const.FRIEND_STATUS_THEY_SENT');
                $user->friend_id = $friend_status_id;
            } else if ($friendOnYourSide && !$friendOnOtherSide) {
                $status = config('const.FRIEND_STATUS_YOU_SENT');
                $user->friend_id = $yourFriend->id;
            }
            $user->friend_status = $status;
        }
        $user->friends = $friends;
        $user->friends_count = count($friends);
        $info = $user->info;
        $info->jobs;
        $info->educates;
        if (!$user) return $this->sendRespondError(
            null,
            'Not found User!',
            config('const.STATUS_CODE_BAD_REQUEST')
        );
        return $this->sendRespondSuccess($user, 'Get User successfully!');
    }

    public function uploadAvatar(ImageRequest $request)
    {
        $uploadFolder = 'avatars/' . auth()->user()->url;
        $image = $request->file('image');
        $name = time() . '_' . $image->getClientOriginalName();
        $image_photo_path = $image->storeAs($uploadFolder, $name, 's3');
        Storage::disk('s3')->setVisibility($image_photo_path, 'public');
        $path = Storage::disk('s3')->url($image_photo_path);
        $user = auth()->user();
        $user->profile_photo_path = $path;
        $user->save();
        return $this->sendRespondSuccess($user, 'Change Avatar Successfully!');
    }

    public function uploadBackground(ImageRequest $request)
    {
        $uploadFolder = 'backgrounds/' . auth()->user()->url;
        $image = $request->file('image');
        $name = time() . '_' . $image->getClientOriginalName();
        $image_photo_path = $image->storeAs($uploadFolder, $name, 's3');
        Storage::disk('s3')->setVisibility($image_photo_path, 'public');
        $path = Storage::disk('s3')->url($image_photo_path);
        $info = auth()->user()->info;
        $info->profile_background_path = $path;
        $info->save();
        return $this->sendRespondSuccess($info, 'Upload file successfully!');
    }

    public function update(ChangeInfoRequest $request)
    {
        $user = auth()->user();
        $param = $request->all();
        if (count($param) == 0) return $this->sendRespondError(null, 'Nothing to update', config('const.STATUS_CODE_BAD_REQUEST'));
        $profile_background_path = Arr::get($param, 'profile_background_path', null);
        $profile_photo_path = Arr::get($param, 'profile_photo_url', null);
        $birthday = Arr::get($param, 'birthday', null);
        $phone_number = Arr::get($param, 'phone_number', null);
        $live_at = Arr::get($param, 'live_at', null);
        $from = Arr::get($param, 'from', null);
        $link_to_social = Arr::get($param, 'link_to_social', null);
        $url = Arr::get($param, 'url', null);
        $story = Arr::get($param, 'story', null);
        $story_privacy = Arr::get($param, 'story_privacy', null);
        $locale = Arr::get($param, 'locale', null);
        $name = Arr::get($param, 'name', null);
        $info = $user->info;
        if ($profile_background_path) $info->profile_background_path = $profile_background_path;
        if ($profile_photo_path) $user->profile_photo_path = $profile_photo_path;
        if ($birthday) $info->birthday = $birthday;
        if ($phone_number) $info->phone_number = $phone_number;
        if ($live_at) $info->live_at = $live_at;
        if ($from) $info->from = $from;
        if ($link_to_social) $info->link_to_social = $link_to_social;
        if ($url) $user->url = $url;
        if ($name) $user->name = $name;
        if ($story) {
            $info->story = $story;
            $info->story_privacy = $story_privacy;
        } else if ($story_privacy) $info->story = null;
        if ($locale) $info->locale = $locale;
        $user->save();
        $info->save();
        return $this->sendRespondSuccess($user, 'Update Info successfully!');
    }

    public function removeBackground()
    {
        $info = auth()->user()->info;
        $info->profile_background_path = null;
        $info->save();
    }

    public function checkUrl(CheckUrlRequest $request)
    {
        if ($request->url) return $this->sendRespondSuccess($request->url, 'Checked!');
        else return $this->sendRespondSuccess(null, 'Checked!');
    }

    public function getInfo(User $user)
    {
        $info = $user->info;
        $info->jobs;
        $info->educates;
        $user->loadCount(['friends' => function ($query) {
            $query->where('status', 1)->where('blocked', 0);
        }]);
        return $this->sendRespondSuccess($user, 'get Info successfully!');
    }
}
