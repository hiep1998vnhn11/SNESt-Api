<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Services\FollowService;

class FollowController extends Controller
{

    private $followService;
    public function __construct(FollowService $followService)
    {
        $this->middleware('auth:api');
        $this->followService = $followService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $follows = $this->followService->search($request->all());
        return $this->sendRespondSuccess($follows);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!isset($request->user_url)) return $this->sendUnvalid([
            'user_url' => 'required!'
        ]);
        $user = $this->findUser($request->user_url);
        if (!$user) return $this->sendRespondError();
        $this->followService->follow($user->id);
        return $this->sendRespondSuccess([
            'full_name' => $user->full_name,
        ], 'success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if (!isset($request->user_url)) return $this->sendUnvalid([
            'user_url' => 'required!'
        ]);
        $user = $this->findUser($request->user_url);
        if (!$user) return $this->sendRespondError();
        $this->followService->unFollow($user->id);
        return $this->sendRespondSuccess([
            'full_name' => $user->full_name,
        ], 'success');
    }
}
