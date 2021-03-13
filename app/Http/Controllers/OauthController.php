<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Facebook\Facebook;
use App\Models\Social;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class OauthController extends Controller
{
    public function facebook(Request $request)
    {
        $facebook = $request->only('access_token');
        if (!$facebook || !isset($facebook['access_token'])) {
            return $this->sendRespondError($facebook, 'Login facebook fail!', 500);
        }
        // Khởi tạo instance của Facebook Graph SDK
        $fb = new Facebook([
            'app_id' => config('oauth.facebook.app_id'),
            'app_secret' => config('oauth.facebook.app_secret'),
        ]);
        try {
            $response = $fb->get('/me?fields=id,name,email,link,birthday', $facebook['access_token']); // Lấy thông tin 
            // user facebook sử dụng access_token được gửi lên từ client
            $profile = $response->getGraphUser();
            if (!$profile || !isset($profile['id'])) { // Nếu access_token không lấy đc thông tin hợp lệ thì trả về login false luôn
                return $this->sendRespondError($response, 'Login facebook fail!', 500);
            }
            $email = $profile['email'] ?? null;
            $social = Social::where('social_id', $profile['id'])->where('type', config('user.social_network.type.facebook'))->first();
            // Lấy được userId của Facebook ta kiểm tra trong bảng social_networks đã có chưa, nếu có thì tài khoản facebook này 
            // đã từng đăng nhập vào hệ thống ta chỉ cần lấy ra user rồi generate jwt trả về cho client; Ngược lại nếu chưa có thì 
            // ta sẽ tiếp tục dùng email trả về từ facebook kiểm tra xem nếu có user với email như thế rồi thì lấy luôn user đó nếu 
            // không thì tạo user mới với email trên và tạo bản ghi social_network lưu thông tin userId của facebook rồi generate jwt
            // để trả về cho client
            if ($social) {
                $user = $social->user;
            } else {
                $user = $email ? User::firstOrCreate(['email' => $email]) : User::create();
                $user->socialNetwork()->create([
                    'social_id' => $profile['id'],
                    'type' => config('user.social_network.type.facebook'),
                ]);
                $user->name = $profile['name'];
                $user->save();
            }

            $token = JWTAuth::fromUser($user);

            return $this->responseSuccess(compact('token', 'user'));
        } catch (\Exception $e) {
            Log::error('Error when login with facebook: ' . $e->getMessage());
            return $this->responseErrors(config('code.user.login_facebook_failed'), trans('messages.user.login_facebook_failed'));
        }
    }
}
