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
        return $this->respondWithToken(auth()->refresh());
    }

    public function register(RegisterRequest $request)
    {
        if (Str::contains($request->name, 'admin') || Str::contains($request->email, 'admin')) {
            $message = 'Name or Email must not contain \'admin\' String!';
            return $this->sendRespondError($request->all(), $message, config('const.STATUS_CODE_BAD_REQUEST'));
        }
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->url = Str::random(15);
        switch ($request->gender) {
            case 'male':
                $user->profile_photo_path = 'https://www.pcjrchargers.com/sites/default/files/default_images/default-user_0.png';
                break;
            case 'female':
                $user->profile_photo_path = 'https://termpapersite.com/wp-content/uploads/2020/06/client-2.jpg';
                break;
            default:
                return $this->sendRespondError($request->gender, 'Gender error!', config('const.STATUS_CODE_BAD_REQUEST'));
                break;
        }

        $messageSuccess = 'Register successfully!';
        $role = Role::findById(1);
        $user->assignRole($role);
        $user->save();
        $info = new Info;
        $info->user_id = $user->id;
        $info->profile_background_path = 'https://www.reachaccountant.com/wp-content/uploads/2016/06/Default-Background.png';
        $info->birthday = $request->birthday;
        $info->gender = $request->gender;
        $info->save();
        $user->info;
        return $this->sendRespondSuccess($user, $messageSuccess);
    }
}
