<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\FriendNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class FriendController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function create()
    {
        $user = User::find(1);
        auth()->user()->notify(new FriendNotification($user));
        return $this->sendRespondSuccess(null, 'Create successfully!');
    }

    public function read()
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function delete()
    {
    }

    public function get()
    {
        $unread = auth()->user()->unreadNotifications;
        $notifications = auth()->user()->notifications()->orderBy('read_at', 'desc')->paginate(20);
        $unread->markAsRead();
        return $this->sendRespondSuccess($notifications, 'Notifications');
    }

    public function numberUnread()
    {
        $unread = auth()->user()->unreadNotifications;
        return $this->sendRespondSuccess(count($unread), 'Amount of unread notifications');
    }
}
