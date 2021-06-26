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
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterMail;
use App\Models\Vertication;
use Carbon\Carbon;

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
    public function login(Request $request)
    {
        $credentials = request(['email', 'password']);
        if (!$token = auth()->attempt($credentials)) {
            return $this->sendRespondError($credentials, 'Unauthorized', config('const.STATUS_CODE_UNAUTHORIZED'));
        }
        if (auth()->user()->active !== 1) return $this->sendRespondError($request->email, 'Unconfirmable', 400);
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
        $token = auth()->refresh(true, true);
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
        $user->active = 0;
        $role = Role::findById(1);
        $user->assignRole($role);
        $user->save();
        $info = new Info;
        $info->user_id = $user->id;
        $info->save();
        $user->info;

        $code = random_int(100000, 999999);
        $details = [
            'datetime' => now(),
            'title' => 'Snest - đăng ký',
            'header' => 'Cảm ơn bạn đã đăng ký vào Snest',
            'content' => 'Đây là mã xác minh của bạn: ' . $code
        ];
        Vertication::create([
            'code' => $code,
            'user_id' => $user->id,
            'expire' => Carbon::now()->addDay(1)
        ]);
        Mail::to($request->email)->send(new RegisterMail($details));
        return $this->sendRespondSuccess();
    }
}
