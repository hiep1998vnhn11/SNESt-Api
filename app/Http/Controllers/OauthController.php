<?php

namespace App\Http\Controllers;

use App\Http\Requests\FacebookOauthRequest;
use Illuminate\Http\Request;
use Facebook\Facebook;
use App\Models\Social;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Info;

class OauthController extends Controller
{
    public function facebook(FacebookOauthRequest $request)
    {
        $access_token = $request->access_token;
        $facebookInstance = new Facebook([
            'app_id' => config('oauth.facebook.app_id'),
            'app_secret' => config('oauth.facebook.app_secret'),
        ]);
        try {
            $response = $facebookInstance->get('/me?fields=id,picture.width(1000),name,email,link,birthday,gender', $access_token);
            $facebookUser = $response->getGraphUser();
            if (!$facebookUser || !isset($facebookUser['id'])) {
                return $this->sendRespondError($response, 'Login facebook fail!', 500);
            }
            $email = $facebookUser['email'] ?? null;
            $social = Social::where('provider_id', $facebookUser['id'])
                ->where('provider_oauth', config('oauth.facebook.type'))
                ->first();
            $user = null;
            if ($social) {
                $user = $social->user;
            } else if ($email) {
                $user = new User();
                $user->email = $email;
                $user->name = $facebookUser['name'];
                $user->password = 1;
                $user->url = $facebookUser['id'];
                $user->profile_photo_path = $facebookUser['picture']['url'];
                $user->save();
                $role = Role::findById(1);
                $user->assignRole($role);
                $user->save();
                $info = new Info;
                $info->user_id = $user->id;
                $info->profile_background_path = 'https://www.reachaccountant.com/wp-content/uploads/2016/06/Default-Background.png';
                $info->birthday = $facebookUser['birthday'];
                $info->gender = $facebookUser['gender'];
                $info->save();
                $social = new Social();
                $social->user_id = $user->id;
                $social->provider_oauth = config('oauth.facebook.type');
                $social->provider_id = $facebookUser['id'];
                $social->save();
            } else {
                return $this->sendRespondError($facebookUser, 'This user not have email!', 500);
            }
            if (!$token = auth()->setTTL(7200)->tokenById($user->id)) {
                return $this->sendRespondError($user, 'Unauthorized', config('const.STATUS_CODE_UNAUTHORIZED'));
            }
            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            Log::error('Error when login with facebook: ' . $e->getMessage());
            return $this->sendRespondError($access_token, $e->getMessage(), 500);
        }
    }
}
