<?php

namespace App\Http\Controllers;

use App\Http\Requests\FacebookOauthRequest;
use App\Http\Requests\GoogleOauthRequest;
use Illuminate\Http\Request;
use Facebook\Facebook;
use Google\Client;
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
                return $this->sendRespondError($response, 'Unauthorized', config('const.STATUS_CODE_UNAUTHORIZED'));
            }
            $email = $facebookUser['email'] ?? null;
            $social = Social::where('provider_id', $facebookUser['id'])
                ->where('provider_oauth', config('oauth.facebook.type'))
                ->first();
            $user = null;
            if ($social) {
                $user = $social->user;
            } else {
                $user = User::where('email', $email)->first();
                if (!$user) {
                    $user = new User();
                    $user->email = $email;
                    $user->name = $facebookUser['name'];
                    $user->password = null;
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
                }
                $social = new Social();
                $social->user_id = $user->id;
                $social->provider_oauth = config('oauth.facebook.type');
                $social->provider_id = $facebookUser['id'];
                $social->save();
            }
            // else {
            //     return $this->sendRespondError($facebookUser, 'This user not have email!', 500);
            // }
            if (!$token = auth()->setTTL(7200)->tokenById($user->id)) {
                return $this->sendRespondError($user, 'Unauthorized', config('const.STATUS_CODE_UNAUTHORIZED'));
            }
            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            return $this->sendRespondError($access_token, $e->getMessage(), 500);
        }
    }

    public function google(GoogleOauthRequest $request)
    {
        $idToken = $request->id_token;
        try {
            $client = new Client(['client_id' => config('oauth.google.client_id')]);
            $googleUser = $client->verifyIdToken($idToken);
            if (!$googleUser) {
                return $this->sendRespondError($idToken, 'Unauthorized', config('const.STATUS_CODE_UNAUTHORIZED'));
            }
            $social = Social::where('provider_id', $googleUser['sub'])
                ->where('provider_oauth', config('oauth.google.type'))
                ->first();
            $user = null;
            if ($social) {
                $user = $social->user;
            } else {
                $user = User::where('email', $googleUser['email'])->first();
                if (!$user) {
                    $user = new User();
                    $user->email = $googleUser['email'];
                    $user->name = $googleUser['name'];
                    $user->password = null;
                    $user->url = $googleUser['sub'];
                    $user->profile_photo_path = $googleUser['picture'];
                    $user->save();
                    $role = Role::findById(1);
                    $user->assignRole($role);
                    $user->save();
                    $info = new Info;
                    $info->user_id = $user->id;
                    $info->profile_background_path = 'https://www.reachaccountant.com/wp-content/uploads/2016/06/Default-Background.png';
                    $info->birthday = null;
                    $info->save();
                }
                $social = new Social();
                $social->user_id = $user->id;
                $social->provider_oauth = config('oauth.google.type');
                $social->provider_id = $googleUser['sub'];
                $social->save();
            }
            if (!$token = auth()->setTTL(7200)->tokenById($user->id)) {
                return $this->sendRespondError($user, 'Unauthorized', config('const.STATUS_CODE_UNAUTHORIZED'));
            }
            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            return $this->sendRespondError($idToken, $e->getMessage(), 500);
        }
    }
}
