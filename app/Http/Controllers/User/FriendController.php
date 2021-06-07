<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\FriendRequest;
use Illuminate\Http\Request;
use App\Http\Services\FriendService;
use App\Models\Friend;
use App\Models\User;
use App\Notifications\FriendNotification;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class FriendController extends Controller
{
    private $friendService;

    public function __construct(FriendService $friendService)
    {
        $this->friendService = $friendService;
    }

    public function addFriend(String $url)
    {
        $user = User::where('url', $url)->firstOrFail();
        $relation = Friend::where('user_id', $user->id)
            ->where('friend_id', auth()->user()->id)
            ->first();
        if ($relation && $relation->status === 3) return $this->sendForbidden();
        $friend = Friend::where('user_id', auth()->user()->id)
            ->where('friend_id', $user->id)
            ->first();
        if ($friend && $friend->status == 1) return $this->sendForbidden();
        if (!$friend) {
            $friend = Friend::create([
                'user_id' => auth()->user()->id,
                'friend_id' => $user->id,
                'status' => 2
            ]);
        } else {
            $friend->status = 2;
            $friend->save();
        }
        $this->sendFriendNotificationToUser(auth()->user(), $user);
        return $this->sendRespondSuccess($friend, 'Handle success!');
    }

    public function denied(Friend $friend)
    {
        // Forbidden of user
        if ($friend->friend_id != auth()->user()->id) return $this->sendForbidden();

        // fail!
        if ($friend->status != 1 && $friend->blocked != 0)
            return $this->sendRespondError($friend, 'Handle friend fail!', config('const.STATUS_CODE_UN_PROCESSABLE'));
        $friend->status = 0;
        $friend->save();
        return $this->sendRespondSuccess($friend, 'Denied friend successfully');
    }

    public function get(Request $request)
    {
        $searchKey = Arr::get($request->all(), 'search_key', null);
        $param = auth()->user()->friends()
            ->where('status', 1)
            ->where('blocked', 0)
            ->with('user_friend');
        if (!$searchKey) $friends = $param->get();
        else $friends = $param->whereHas('user_friend', function ($q) use ($searchKey) {
            $q->where('name', 'like', '%' . $searchKey . '%');
        })->get();
        return $this->sendRespondSuccess($friends, 'Get friend successfully!');
    }

    public function store(Request $request)
    {
        $status = Arr::get($request->all(), 'type', null);
        $relationships = auth()->user()->relationships();
        if ($status) {
            $relationships->where('status', strval($status));
        }
        $relationships->paginate(config('const.DEFAULT_PER_PAGE'));
        return $this->sendRespondSuccess($relationships, 'Store Relationships!');
    }

    public function aaddFriend(User $user)
    {
        $relation = auth()->user()->relationships()->where('friend_id', $user->id)->first();
        if (!$relation) {
            $relation = new Friend();
            $relation->user_id = auth()->user()->id;
            $relation->friend_id = $user->id;
            $relation->status = config('const.FRIEND_STATUS_PENDING');
            $relation->save();
            $user->notify(new FriendNotification([
                'username' => auth()->user()->name,
                'image' => auth()->user()->profile_photo_path,
                'relationship' => $relation,
                'url' => auth()->user()->url,
                'status' => 'requesting',
            ]));
            return $this->sendRespondSuccess($relation, 'Add friend success!');
        }
        if ($relation->status == config('const.FRIEND_STATUS_FRIEND'))
            return $this->sendRespondError($relation, 'Been friend!', config('const.STATUS_CODE_BAD_REQUEST'));
        $friendRelation = $user->relationships()->where('friend_id', auth()->user()->id)->first();
        if ($friendRelation && $friendRelation->status == config('const.FRIEND_STATUS_BLOCKED')) return $this->sendBlocked();
        if ($friendRelation && $friendRelation->status == config('const.FRIEND_STATUS_PENDING')) {
            $friendRelation->status = config('const.FRIEND_STATUS_FRIEND');
            $friendRelation->save();
            $relation->status = config('FRIEND_STATUS_FRIEND');
            $relation->save();
            return $this->sendRespondSuccess($relation, 'Friend success!');
        }
        $relation->status = config('const.FRIEND_STATUS_PENDING');
        $relation->save();
        $notification = $user->notifications()
            ->where('type', 'App\Notifications\FriendNotification')
            ->where('data->relationship->user_id', auth()->user()->id)
            ->first();
        if ($notification) {
            $notification->data = [
                'username' => auth()->user()->name,
                'image' => auth()->user()->profile_photo_path,
                'url' => auth()->user()->url,
                'status' => 'requesting',
                'relationship' => $relation
            ];
            $notification->updated_at = Carbon::now();
            $notification->read_at = null;
            $notification->save();
        } else {
            $user->notify(new FriendNotification([
                'username' => auth()->user()->name,
                'image' => auth()->user()->profile_photo_path,
                'status' => 'requesting',
                'relationship' => $relation
            ]));
        }
        return $this->sendRespondSuccess($relation, 'Add friend success!');
    }

    public function acceptFriend(String $url)
    {
        $user = User::where('url', $url)->firstOrFail();
        $friend = Friend::where('user_id', $user->id)
            ->where('friend_id', auth()->user()->id)
            ->firstOrFail();
        if ($friend->status !== 2) return $this->sendRespondError();
        $relation = Friend::where('user_id', auth()->user()->id)
            ->where('friend_id', $user->id)
            ->first();
        if (!$relation) {
            $relation = Friend::create([
                'user_id' => auth()->user()->id,
                'friend_id' => $user->id,
                'status' => 1
            ]);
        } else {
            $relation->status = 1;
            $relation->save();
        }
        $friend->status = 1;
        $friend->save();
        $this->sendFriendNotificationToUser(auth()->user(), $user, 'accepted');
        return $this->sendRespondSuccess($relation, 'Accepted');
    }

    public function cancelFriend(string $url)
    {
        $user = User::where('url', $url)->firstOrFail();
        $friend =  $friend = Friend::where('user_id', $user->id)
            ->where('friend_id', auth()->user()->id)
            ->firstOrFail();
        if ($friend->status !== 2) return $this->sendRespondError();
        $relation = Friend::where('user_id', auth()->user()->id)
            ->where('friend_id', $user->id)
            ->first();
        if (!$relation) {
            $relation = Friend::create([
                'user_id' => auth()->user()->id,
                'friend_id' => $user->id,
                'status' => 0
            ]);
        } else {
            $relation->status = 0;
            $relation->save();
        }
        $friend->status = 0;
        $friend->save();
        return $this->sendRespondSuccess($friend, 'Cancel friend successfully');
    }

    public function cancelFriendRequest(String $url)
    {
        $user = User::where('url', $url)->firstOrFail();
        $friend = Friend::where('user_id', auth()->user()->id)
            ->where('friend_id', $user->id)
            ->firstOrFail();
        if ($friend->status !== 2) return $this->sendRespondError();
        $friend->status = 0;
        $friend->save();
        return $this->sendRespondSuccess($friend, 'Handle success!');
    }

    public function blockFriend(String $url)
    {
        $user = User::where('url', $url)->firstOrFail();
        $friend = Friend::where('user_id', auth()->user()->id)
            ->where('friend_id', $user->id)
            ->first();
        if (!$friend) {
            $friend = Friend::create([
                'user_id' => auth()->user()->id,
                'friend_id' => $user->id,
                'status' => 3
            ]);
        } else {
            $friend->status = 3;
            $friend->save();
        }
        $relation = Friend::where('user_id', $user->id)
            ->where('friend_id', auth()->user()->id)
            ->first();
        if ($relation && ($relation->status == 1 || $relation->status == 2)) {
            $relation->status == 0;
            $relation->save();
        }
        return $this->sendRespondSuccess($friend, 'Handle success!');
    }

    public function unfriend(String $url)
    {
        $user = User::where('url', $url)->firstOrFail();
        $friend = Friend::where('user_id', auth()->user()->id)
            ->where('friend_id', $user->id)
            ->firstOrFail();
        $relation = Friend::where('user_id', $user->id)
            ->where('friend_id', auth()->user()->id)
            ->firstOrFail();
        if ($friend->status !== 1 || $relation->status !== 1) return $this->sendForbidden();
        $friend->status = 0;
        $friend->save();
        $relation->status = 0;
        $relation->save();
        return $this->sendRespondSuccess($friend, 'Handle success!');
    }

    public function sendFriendNotificationToUser($userRequest, $user, $type = 'requesting')
    {
        $notification = $user->notifications()
            ->where('type', 'App\Notifications\FriendNotification')
            ->where('data->user_url', $userRequest->url)
            ->where('data->type', $type)
            ->first();
        if ($notification) {
            $notification->updated_at = Carbon::now();
            $notification->read_at = null;
            $notification->save();
            return;
        }
        $notification = $user->notify(new FriendNotification([
            'username' => $userRequest->full_name,
            'user_url' => $userRequest->url,
            'type' => $type
        ]));
    }
}
