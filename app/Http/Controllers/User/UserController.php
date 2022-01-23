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
use App\Models\Follow;
use App\Models\Friend;
use App\Models\Post;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
                config('const.STATUS_CODE_NOT_FOUND')
            );
        $user = $this->findUser($user_url);
        if (!$user) return $this->sendRespondError(
            null,
            'Not found User!',
            config('const.STATUS_CODE_NOT_FOUND')
        );
        $user->friends = $user->friends()->take(config('constant.DEFAULT_FRIEND_PER_PAGE'))->get();
        $user->loadCount('friends');
        $info = $user->info;
        $info->jobs;
        $info->educates;
        return $this->sendRespondSuccess($user, 'Get User successfully!');
    }
    public function getForAuth(GuestUserRequest $request)
    {
        $user_url = $request->user_url;
        if ($user_url == auth()->user()->url) $user = auth()->user();
        else $user = $this->findUser($user_url);
        if (!$user) return $this->sendRespondError();
        $info = $user->info;
        return $this->sendRespondSuccess($user, 'Get User successfully!');
    }

    public function uploadAvatar(ImageRequest $request)
    {
        $uploadFolder = 'public/avatars/' . auth()->user()->url;
        $image = $request->file('image');
        $name = time() . '_' . $image->getClientOriginalName();
        $this->log($image->getClientOriginalExtension());
        $image_photo_path = $image->storeAs($uploadFolder, $name);
        Storage::disk('local')->setVisibility($image_photo_path, 'public');
        $path = Storage::disk('local')->url($image_photo_path);
        $user = auth()->user();
        $user->profile_photo_path = config('app.url') . $path;
        $user->save();
        return $this->sendRespondSuccess($user->profile_photo_path, 'Change Avatar Successfully!');
    }

    public function uploadBackground(ImageRequest $request)
    {
        $uploadFolder = 'public/backgrounds/' . auth()->user()->url;
        $image = $request->file('image');
        $name = time() . '_' . $image->getClientOriginalName();
        $image_photo_path = $image->storeAs($uploadFolder, $name);
        Storage::disk('local')->setVisibility($image_photo_path, 'public');
        $path = Storage::disk('local')->url($image_photo_path);
        $info = auth()->user()->info;
        $info->profile_background_path = config('app.url') . $path;
        $info->save();
        return $this->sendRespondSuccess($info->profile_background_path, 'Upload file successfully!');
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
        $user = User::where('url', $request->url)->first();
        if ($user) return $this->sendRespondError();
        return $this->sendRespondSuccess();
    }

    public function getInfo(String $url)
    {
        $user = User::where('url', $url)
            ->select('id', 'url', 'full_name', 'profile_photo_path')
            ->firstOrFail();
        $user->loadCount([
            'friends' => function ($query) {
                $query->where('status', 1);
            },
            'follows', 'followeds'
        ]);
        $friend = Friend::where('user_id', auth()->user()->id)
            ->where('friend_id', $user->id)
            ->first();
        $user->friend = $friend ? $friend->status : null;
        $user->info;

        $follow = Follow::where('user_id', auth()->user()->id)
            ->where('followed_id', $user->id)
            ->first();
        $user->follow = $follow ? $follow->status : null;
        return $this->sendRespondSuccess($user, 'get Info successfully!');
    }

    public function get(String  $url)
    {
        $user = User::where('url', $url)
            ->leftJoin('infos', 'infos.user_id', 'id')
            ->first();
        if (!$user) return $this->sendRespondError();
        $user->loadCount(['friends', 'follows', 'followeds']);
        $friends = Friend::where('user_id', $user->id)
            ->where('status', 1)
            ->orderBy('updated_at', 'desc')
            ->limit(config('const.DEFAULT_PER_PAGE'))
            ->get();
        if (auth()->user()) {
            $friendStatus = Friend::where('user_id', auth()->user()->id)
                ->where('friend_id', $user->id)
                ->first();
            return $this->sendRespondSuccess([
                'user' => $user,
                'friends' => $friends,
                'friend_status' => $friendStatus
            ]);
        }
        return $this->sendRespondSuccess([
            'user' => $user,
            'friends' => $friends,
        ]);
    }

    public function getPost(Request $request, String $url)
    {
        $user = User::where('url', $url)->firstOrFail();
        $params = $request->all();
        $offset = Arr::get($params, 'offset', 0);
        $limit = Arr::get($params, 'limit', 5);
        $posts = Post::where('posts.user_id', $user->id)
            ->with(['images', 'media'])
            ->leftJoin('users', 'users.id', 'posts.user_id')
            ->leftJoin(
                'likes',
                function ($join) {
                    $join->on('likes.likeable_id', '=', 'posts.id')
                        ->where('likes.likeable_type', '=', 'App\Models\Post')
                        ->where('likes.user_id', auth()->user()->id);
                }
            )
            ->select(
                'posts.id',
                'posts.uid',
                'posts.content',
                'posts.updated_at',
                'posts.created_at',
                'posts.privacy',
                'users.full_name as user_name',
                'users.profile_photo_path as user_profile_photo_path',
                'users.url as user_url',
                'posts.image_count',
                'likes.status as like_status',
                DB::raw('(SELECT count(*) FROM comments WHERE posts.id = comments.post_id) as comments_count'),
                DB::raw("(SELECT count(*) FROM likes WHERE posts.id = likes.likeable_id AND likes.likeable_type = 'post' AND likes.status > 0) as likes_count"),
            )
            ->orderBy('updated_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(function ($query) {
                $query->setRelation('media', $query->media->take(4));
                return $query;
            });
        return $this->sendRespondSuccess($posts);
    }

    public function getFriend(Request $request, String $url)
    {
        $user = User::where('url', $url)->firstOrFail();
        $params = $request->all();
        $limit = Arr::get($params, 'limit', config('const.DEFAULT_PER_PAGE'));
        $searchKey = Arr::get($params, 'search_key', null);
        $type = Arr::get($params, 'type', config('snest.friends.type.default'));
        $types = config('snest.friends.types');
        if (!isset($types[$type])) $type = $types['default'];
        $friends = Friend::query()
            ->where('friends.user_id', $user->id)
            ->leftJoin('users as UF', 'friends.friend_id', 'UF.id');
        $friends = $friends->where('friends.status', $types[$type]);
        if ($types[$type] != 'all') {
            $friends = $friends;
        }
        if ($searchKey) {
            $friends = $friends->where(function ($query) use ($searchKey) {
                $query->where('UF.full_name', 'like', '%' . $searchKey . '%')
                    ->orWhere('UF.slug', 'like', '%' . $searchKey . '%')
                    ->orWhere('UF.phone_number', 'like', '%' . $searchKey . '%')
                    ->orWhere('UF.url', 'like', '%' . $searchKey . '%');
            });
        }
        $friends = $friends
            ->select('UF.full_name', 'UF.url', 'UF.profile_photo_path', 'friends.id as id')
            ->paginate($limit);
        return $this->sendRespondSuccess([
            'data' => $friends->items(),
            'total' => $friends->total(),
        ]);
    }

    public function searchFollow(Request $request)
    {
        $params = $request->all();
        $searchKey = Arr::get($params, 'search_key', null);
        $limit = Arr::get($params, 'limit', config('const.DEFAULT_PER_PAGE'));
        $users = Follow::where('user_id', auth()->user()->id)
            ->leftJoin('users', 'follows.followed_id', 'users.id');
        if ($searchKey) {
            $users = $users->where(function ($query) use ($searchKey) {
                $query->where('users.name', 'like', '%' . $searchKey . '%')
                    ->orWhere('users.slug', 'like', '%' . $searchKey . '%')
                    ->orWhere('users.url', 'like', '%' . $searchKey . '%')
                    ->orWhere('users.phone_number', 'like', '%' . $searchKey . '%');
            });
        }
        $users = $users->limit($limit)->get();
        return $this->sendRespondSuccess($users);
    }

    public function suggestUser(Request $request)
    {
        $params = $request->all();
        $offset = Arr::get($params, 'offset', 0);
        $limit = Arr::get($params, 'limit', config('const.DEFAULT_PER_PAGE'));
        $followList = Follow::where('user_id', auth()->user()->id)
            ->where('status', 1)
            ->pluck('followed_id');
        $followList[] = auth()->user()->id;
        $users = User::query()
            ->whereNotIn('id', $followList)
            ->orderBy('updated_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->select(
                'full_name',
                'url',
                'profile_photo_path'
            )
            ->get();
        $this->log($users);
        return $this->sendRespondSuccess($users);
    }

    public function followUser(Request $request)
    {
        $params = $request->all();
        $offset = Arr::get($params, 'offset', 0);
        $limit = Arr::get($params, 'limit', config('const.DEFAULT_PER_PAGE'));
        $users = User::query()
            ->leftJoin('follows', 'follows.followed_id', 'users.id')
            ->where('follows.user_id', auth()->user()->id)
            ->where('follows.status', 1)
            ->where('users.id', '<>', auth()->user()->id)
            ->orderBy('follows.updated_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->select('users.full_name', 'users.url', 'users.profile_photo_path')
            ->get();
        return $this->sendRespondSuccess($users);
    }

    public function changeUrl(Request $request)
    {
        $url = $request->url ? $request->url : '';
        $currentUser = auth()->user();
        if ($url == $currentUser->url) return $this->sendRespondSuccess('Change url success!');
        $user = User::where('url', $url)->first();
        if ($user) return $this->sendRespondError();
        $currentUser->url = $url;
        $currentUser->save();
        return $this->sendRespondSuccess('Change url success');
    }
}
