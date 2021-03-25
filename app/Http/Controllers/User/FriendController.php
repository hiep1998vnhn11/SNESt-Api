<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\FriendRequest;
use App\Http\Requests\StoreFriendRequest;
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

    public function handleFriend(FriendRequest $request, User $user)
    {
        $relation = auth()->user()->friends()
            ->where('friend_id', $user->id)
            ->first();
        $data = $this->friendService->handleFriend($request->all());
        if (!$data) return $this->sendRespondError(null, 'handle failed!', config('const.STATUS_CODE_UN_PROCESSABLE'));
        else {
            $friend = User::findOrFail($data->friend_id);
            $isNotification = false;
            $noti = null;
            foreach ($friend->notifications as $notification) {
                if ($notification->data["id"] == $data->id) {
                    $isNotification = true;
                    $noti = $notification;
                    break;
                }
            }
            if (!$isNotification) {
                $noti = $friend->notify(new FriendNotification([
                    'id' => $data->id,
                    'status' => 'pending',
                    'user' => $data->user
                ]));
            } else {
                $noti->updated_at = Carbon::now();
                $noti->read_at = null;
                $noti->save();
            }
            return $this->sendRespondSuccess($noti, 'Handle success!');
        }
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

    public function addFriend(User $user)
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
                'status' => 'requesting',

            ]));
            return $this->sendRespondSuccess($relation, 'Add friend success!');
        }

        if ($relation->status == config('const.FRIEND_STATUS_FRIEND'))
            return $this->sendRespondError($relation, 'Been friend!', config('const.STATUS_CODE_BAD_REQUEST'));

        $friendRelation = $user->relationships()->where('friend_id', auth()->user()->id)->first();
        if ($friendRelation && $friendRelation->status == config('const.FRIEND_STATUS_BLOCKED')) return $this->sendBlocked();
        if ($friendRelation->status == config('const.FRIEND_STATUS_PENDING')) {
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

    public function accept(Friend $friend, Request $request)
    {
        // $friend này là của thằng gửi đến, phải check kỹ
        // Forbidden of user
        if ($friend->friend_id != auth()->user()->id) return $this->sendForbidden();
        if ($friend->status != config('const.FRIEND_STATUS_PENDING'))
            return $this->sendRespondError($friend, 'Accept Fail!', config('const.STATUS_CODE_UN_PROCESSABLE'));

        $relation = auth()->user()->relationships()->where('friend_id', $friend->user_id)->first();
        if (!$relation) {
            $relation = new Friend();
            $relation->user_id = auth()->user()->id;
            $relation->friend_id = $friend->user_id;
            $relation->status = config('const.FRIEND_STATUS_FRIEND');
            $relation->save();
        }
        $friend->status = config('const.FRIEND_STATUS_FRIEND');
        $friend->save();
        $friend->user->notify(new FriendNotification([
            'username' => auth()->user()->name,
            'image' => auth()->user()->profile_photo_path,
            'status' => 'accepted',
            'relationship' => $relation
        ]));
        $notification = auth()->user()->notifications()
            ->where('type', 'App\Notifications\FriendNotification')
            ->where('data->relationship->user_id', $friend->user_id)
            ->first();
        if ($notification) {
            $data = $notification->data;
            $data->relationship = $relation;
            $data->status = 'accepted';
            $notification->data = $data;
            $notification->save();
        }
        return $this->sendRespondSuccess($relation, 'Accepted');
    }

    public function cancel(Friend $friend)
    {
        if ($friend->user_id != auth()->user()->id) return $this->sendForbidden();
        if ($friend->status != 0) return $this->sendRespondError($friend, 'Cancel Failed!', config('const.STATUS_CODE_UN_PROCESSABLE'));
        $friend->status = config('const.FRIEND_STATUS_NONE');
        $friend->save();
        $notification = $friend->user_friend->notifications()
            ->where('type', 'App\Notifications\FriendNotification')
            ->where('data->relationship->user_id', auth()->user()->id)
            ->first();
        if ($notification) {
            $notification->data = [
                'username' => auth()->user()->name,
                'image' => auth()->user()->image,
                'relationship' => $friend,
                'status' => 'canceled'
            ];
            $notification->save();
        }
        return $this->sendRespondSuccess($friend, 'Cancel friend successfully');
    }
}
