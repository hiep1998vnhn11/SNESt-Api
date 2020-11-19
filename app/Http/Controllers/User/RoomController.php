<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
    public function get()
    {
        $rooms = auth()->user()->rooms;
        foreach ($rooms as $room) {
            $room->user_with;
        }
        return $this->sendRespondSuccess(auth()->user(), 'Get Room for user Successfully!');
    }

    public function store()
    {
        $rooms = auth()->user()->rooms;
        foreach ($rooms as $room) {
            $room->user_with;
        }
        return $this->sendRespondSuccess($rooms, 'Successfully!');
    }
}
