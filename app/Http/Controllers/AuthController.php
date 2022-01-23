<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmRegisterRequest;
use App\Http\Requests\EmailRequest;
use App\Http\Requests\LoginRequest;
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
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'confirmRegister', 'resendVerticationCode', 'forgotPassword']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $params = $request->validated();
        if (!$request->type || $request->type == 1) {
            $credentials['password'] = $params['password'] ?? null;
            $field = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';
            $credentials[$field] = $params['email'] ?? null;
            if (!$token = auth()->attempt($credentials)) {
                return $this->sendRespondError($credentials, 'Unauthorized', config('const.STATUS_CODE_UNAUTHORIZED'));
            }
        } elseif ($request->type == 2) {
            $token = $request->token ?? null;
            if (!$token) {
                return $this->sendRespondError(null, 'Unauthorized', config('const.STATUS_CODE_UNAUTHORIZED'));
            }
            $fb = new Facebook([
                'app_id' => config('social.facebook.app_id'),
                'app_secret' => config('social.facebook.app_secret'),
                'default_graph_version' => 'v2.10',
            ]);
            try {
                $response = $fb->get('/me?fields=id,name,email,first_name,last_name', $request->token);
            } catch (FacebookResponseException $e) {
                $this->log($e);
                return $this->sendRespondError(null, 'Unauthorized', config('const.STATUS_CODE_UNAUTHORIZED'));
            } catch (FacebookSDKException $e) {
                $this->log($e);
                return $this->sendRespondError(null, 'Unauthorized', config('const.STATUS_CODE_UNAUTHORIZED'));
            } catch (\Exception $e) {
                $this->log($e);
            }
            $userInfo = $response->getGraphUser();
            if (!$userInfo) {
                return $this->sendRespondError(null, 'Unauthorized', config('const.STATUS_CODE_UNAUTHORIZED'));
            }

            $oauthId = $userInfo['id'] ?? null;
            $user = User::where('provider_oauth_id', $oauthId)
                ->where('provider_oauth', 'facebook')
                ->first();
            if (!$user) {
                $user = User::where('email', $userInfo['email'])
                    ->first();
            }
            if (!$user) {
                $user = new User();
                $user->first_name = $userInfo['first_name'] ?? null;
                $user->last_name = $userInfo['last_name'] ?? null;
                $user->full_name = $userInfo['name'] ?? null;
                $user->slug = Str::slug($user->full_name);
                $user->email = $userInfo['email'] ?? null;
                $user->url = $userInfo['id'];
                $user->provider_oauth_id = $userInfo['id'];
                $user->provider_oauth = 'facebook';
                $role = Role::findById(1);
                $user->assignRole($role);
                $user->save();
                $info = new Info;
                $info->user_id = $user->id;
                $info->save();
                $user->info = $info;
            }
            $token = auth()->login($user);
        } else {
        }
        // if (auth()->user()->active !== 1) return $this->sendRespondError($request->email, 'Unconfirmable', 400);
        return $this->sendRespondSuccess([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ], 'Login successfully', config('const.STATUS_CODE_SUCCESS'));
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

    public function confirmRegister(ConfirmRegisterRequest $request)
    {
        $email = $request->email;
        $code = $request->code;
        $user = User::where('email', $email)->first();
        if ($user->active === 1) return $this->sendRespondError($email, 'UserHadActived', 400);
        $vertication = Vertication::query()
            ->where('user_id', $user->id)
            ->first();
        $expire = Carbon::create($vertication->expire);
        $now = Carbon::now();
        if ($expire < $now) return $this->sendRespondError($email, 'CodeExpired', 400);
        $user->active = 1;
        $user->save();
        $vertication->delete();
        return $this->sendRespondSuccess($email, 'VertifySuccess');
    }

    public function resendVerticationCode(EmailRequest $request)
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();
        if (!$user) return $this->sendRespondError($email, 'UserNotRegister', 400);
        $vertication = Vertication::where('user_id', $user->id)->first();
        if (!$vertication) return $this->sendRespondError($email, 'UserMustNotVertify', 400);
        $vertication->expire = Carbon::now()->addDay(1);
        $code = random_int(100000, 999999);
        $vertication->code = $code;
        $vertication->save();
        $details = [
            'datetime' => now(),
            'title' => 'Snest - đăng ký',
            'header' => 'Cảm ơn bạn đã đăng ký vào Snest',
            'content' => 'Đây là mã xác minh của bạn: ' . $code
        ];
        Mail::to($request->email)->send(new RegisterMail($details));
        return $this->sendRespondSuccess($email, 'Success');
    }

    public function forgotPassword(EmailRequest $request)
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();
        if (!$user) return $this->sendRespondError($email, 'UserNotRegister', 400);
    }
}
