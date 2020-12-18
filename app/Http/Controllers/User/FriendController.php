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

class FriendController extends Controller
{
    private $friendService;

    public function __construct(FriendService $friendService)
    {
        $this->friendService = $friendService;
    }

    public function handleFriend(FriendRequest $request)
    {
        $message_success = 'Handle friend successfully!';
        $message_error = 'Handle friend fail! Please try again!';
        $data = $this->friendService->handleFriend($request->all());
        if (!$data) return $this->sendRespondError(null, $message_error, config('const.STATUS_CODE_UN_PROCESSABLE'));
        else {
            $friend = User::findOrFail($data->friend_id);
            $isNotification = false;
            $noti = null;
            foreach ($friend->notifications as $notification) {
                if ($notification->data["id"] == $data->id) {
                    $isNotification = true;
                    $noti = $notification;
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
            return $this->sendRespondSuccess($noti, $message_success);
        }
    }

    public function accept(Friend $friend)
    {
        // Forbidden of user
        if ($friend->friend_id != auth()->user()->id) return $this->sendForbidden();

        // fail!
        if ($friend->status != 1 && $friend->blocked != 0)
            return $this->sendRespondError($friend, 'Handle friend fail!', config('const.STATUS_CODE_UN_PROCESSABLE'));
        $new_friend = new Friend();
        $new_friend->user_id = $friend->friend_id;
        $new_friend->friend_id = $friend->user_id;
        $new_friend->status = 1;
        $new_friend->blocked = 0;
        $new_friend->save();

        $noti = $friend->user->notify(new FriendNotification([
            'id' => $new_friend->id,
            'type' => 'accepted',
            'status' => 'accepted',
            'user' => auth()->user()
        ]));
        return $this->sendRespondSuccess($noti, 'Accept friend successfully!');
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

    public function get()
    {
        $friends = auth()->user()->friends()
            ->where('status', 1)
            ->where('blocked', 0)
            ->get();
        foreach ($friends as $friend) {
            $friend->user_friend;
            $friend->user_friend->onlineStatus = $friend->user_friend->isOnline();
        }
        return $this->sendRespondSuccess($friends, 'Get friend successfully!');
    }

    public function cancel(Friend $friend)
    {
        if ($friend->user_id != auth()->user()->id) return $this->sendForbidden();
        if ($friend->status != 1 && $friend->blocked != 0)
            return $this->sendRespondError($friend, 'Handle friend fail!', config('const.STATUS_CODE_UN_PROCESSABLE'));
        $friend->status = 0;
        $friend->save();
        return $this->sendRespondSuccess($friend, 'Denied friend successfully');
    }
}
