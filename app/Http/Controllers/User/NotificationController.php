<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /*
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        $params = $request->all();
        $limit = Arr::get($params, 'limit', 20);
        $offset = Arr::get($params, 'offset', 0);
        $notifications = auth()->user()
            ->notifications()
            ->with(['targetUser'])
            ->orderBy('read_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->take($limit)
            ->get();
        auth()->user()->notifications()
            ->orderBy('read_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->update(['seen_at' => now()]);
        return $this->sendRespondSuccess($notifications, 'Notifications');
    }

    public function numberUnseen()
    {
        $unseenNotifications = auth()->user()
            ->unseenNotifications()
            ->count();
        return $this->sendRespondSuccess($unseenNotifications, 'Amount of unread notifications');
    }

    public function read(Notification $notification)
    {
        if ($notification->user_id != auth()->user()->id) {
            return $this->sendRespondError('You can only read your own notifications');
        }
        $notification->update(['read_at' => now()]);
        return $this->sendRespondSuccess($notification->read_at, 'Notification');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyAll()
    {
        auth()->user()->notifications()->delete();
    }
}
