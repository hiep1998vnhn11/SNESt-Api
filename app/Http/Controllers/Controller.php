<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Carbon\Carbon;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithToken($token)
    {
        return response()->json([
            'status'       => 'success',
            'current'      => Carbon::now()->toDateTimeString(),
            'access_token' => $token,
            'expires_in'   => auth('api')->factory()->getTTL() * 60
        ]);
    }

    /**
     * Send respond Success to Client
     *
     * @param  object $data, string $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendRespondSuccess($data, $message)
    {
        return response()->json([
            'status'  => 'success',
            'current' => Carbon::now()->toDateTimeString(),
            'message' => $message,
            'data'    => $data
        ]);
    }

    /**
     * Send Respond Error to Client.
     *
     * @param  object $data, string $message, int $code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendRespondError($data, $message, $code = 404)
    {
        return response()->json([
            'status'  => 'error',
            'current' => Carbon::now()->toDateTimeString(),
            'message' => $message,
            'data'    => $data
        ], $code);
    }

    /**
     * send respond forbidden to client.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendForbidden()
    {
        return $this->sendRespondError(
            null,
            'Forbidden!',
            config('const.STATUS_CODE_FORBIDDEN')
        );
    }

    /**
     * send respond Blocked to client.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendBlocked()
    {
        return $this->sendRespondError(
            null,
            'This was blocked by admin! Do not try again!',
            config('const.STATUS_CODE_BAD_REQUEST')
        );
    }

    /**
     * check is authenticated user_id.
     *
     * @param Int $user_id 
     *
     * @return Boolean
     */
    public function checkAuthUser($user_id)
    {
        if (auth()->user()->id != $user_id) return true;
        return false;
    }

    /**
     * find User bt user_url.
     *
     * @param String $user_url
     *
     * @return User
     */
    public function findUser($user_url)
    {
        return User::where('url', $user_url)->first();
    }
}
