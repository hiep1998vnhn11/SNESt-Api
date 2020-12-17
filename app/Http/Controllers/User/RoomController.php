<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessageRoomRequest;
use App\Models\Message;
use App\Models\Room;
use Illuminate\Http\Request;
use App\Models\User;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /**
     * Get all room of auth()->user()
     * 
     * @method GET
     *
     * @return Response
     */
    public function get(User $user)
    {
        $room = auth()->user()->rooms()
            ->where('with_id', $user->id)
            ->first();
        if ($room) {
            $room->user_with;
            $room->user_with->onlineStatus = $room->user_with->isOnline();
        }
        return $this->sendRespondSuccess($room, 'Get Room by user Successfully!');
    }

    public function store()
    {
        $rooms = auth()->user()->rooms;
        foreach ($rooms as $room) {
            $room->user_with;
            $room->user_with->onlineStatus = $room->user_with->isOnline();
        }
        return $this->sendRespondSuccess($rooms, 'Successfully!');
    }

    public function create(User $user, MessageRoomRequest $request)
    {
        $isCurrent = auth()->user()->id === $user->id;
        $room_user = new Room();
        $room_user->user_id = auth()->user()->id;
        $room_user->with_id = $user->id;
        $room_user->save();
        $message = new Message();
        $message->room_id = $room_user->id;
        $message->user_id = auth()->user()->id;
        $message->content = $request->content;
        $message->save();

        if ($isCurrent) {
            $room_with = new Room();
            $room_with->user_id = $user->id;
            $room_with->with_id = auth()->user()->id;
            $room_with->save();
            $message = new Message();
            $message->room_id = $room_with->id;
            $message->user_id = auth()->user()->id;
            $message->content = $request->content;
            $message->save();
        }
        return $this->sendRespondSuccess($room_user, 'Create new room and send message successfully!');
    }
}
