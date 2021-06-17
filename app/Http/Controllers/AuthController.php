<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;

use Spatie\Permission\Models\Role;
use App\Http\Requests\RegisterRequest;
use App\Models\Info;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);
        if (!$token = auth()->setTTL(7200)->attempt($credentials)) {
            return $this->sendRespondError($credentials, 'Unauthorized', config('const.STATUS_CODE_UNAUTHORIZED'));
        }
        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $currentUser = auth()->user();
        $currentUser->info->jobs;
        $currentUser->info->educates;
        return $this->sendRespondSuccess(auth()->user(), null);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return $this->sendRespondSuccess(auth()->user(), 'Successfully logged out');
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = auth()->setTTL(7200)->refresh(true, true);
        return $this->respondWithToken($token);
    }

    public function register(RegisterRequest $request)
    {
        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->full_name = $request->first_name . ' ' . $request->last_name;
        $user->slug = Str::slug($user->full_name);
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->url = Str::random(25);
        $messageSuccess = 'Register successfully!';
        $role = Role::findById(1);
        $user->assignRole($role);
        $user->save();
        $info = new Info;
        $info->user_id = $user->id;
        $info->save();
        $user->info;
        return $this->sendRespondSuccess($user, 'Success');
    }
}
