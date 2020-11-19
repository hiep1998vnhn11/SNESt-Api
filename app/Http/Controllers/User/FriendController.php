<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\FriendRequest;
use Illuminate\Http\Request;
use App\Http\Services\FriendService;
use App\Models\Friend;

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
        else return $this->sendRespondSuccess($data, $message_success);
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
        return $this->sendRespondSuccess($new_friend, 'Accept friend successfully!');
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
        }
        return $this->sendRespondSuccess($friends, 'Get friend successfully!');
    }
}
