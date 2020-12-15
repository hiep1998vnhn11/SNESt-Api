<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
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
}
