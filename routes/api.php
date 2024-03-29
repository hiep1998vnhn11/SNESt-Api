<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Guest\TestController;
use App\Http\Controllers\Guest\SearchController as GuestSearchController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\User\CommentController;
use App\Http\Controllers\User\PostController;
use App\Http\Controllers\User\LikeController;
use App\Http\Controllers\User\SubCommentController;
use App\Http\Controllers\User\FriendController;
use App\Http\Controllers\User\MessageController;
use App\Http\Controllers\User\RoomController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\ThreshController;
use App\Http\Controllers\User\SearchController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\Notification\FriendController as NotificationFriendController;
use App\Http\Controllers\OauthController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\User\FollowController;

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\UploadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('facebook/login', [OauthController::class, 'facebook']);
    Route::post('google/login', [OauthController::class, 'google']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('token/refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
    Route::post('confirm-register', [AuthController::class, 'confirmRegister']);
    Route::post('resend-code', [AuthController::class, 'resendVerticationCode']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('confirm-forgot-password', [AuthController::class, 'confirmForgotPassword']);
});

Route::get('test', [ServerController::class, 'api']);

Route::group([
    'prefix' => 'v1'
], function () {
    Route::group([
        'middleware' => 'api',
        'prefix' => 'auth'
    ], function ($router) {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('facebook/login', [OauthController::class, 'facebook']);
        Route::post('google/login', [OauthController::class, 'google']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('token/refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('confirm-register', [AuthController::class, 'confirmRegister']);
        Route::post('resend-code', [AuthController::class, 'resendVerticationCode']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('confirm-forgot-password', [AuthController::class, 'confirmForgotPassword']);
    });

    Route::group([
        'prefix' => 'guest'
    ], function () {
        Route::get('user/{url}', [GuestController::class, 'getUser']);
        Route::get('post/{post}/get', [PostController::class, 'get']);
        Route::get('post/{post}/get_comment', [PostController::class, 'getCommentGuest']);
        Route::get('post/store', [PostController::class, 'store']);
        Route::get('search/identify', [GuestSearchController::class, 'searchUserForIdentify']);
        Route::get('search/trending', [SearchController::class, 'trending']);
    });

    Route::group([
        'prefix' => 'user',
        'middleware' => 'role:viewer|admin'
    ], function () {
        Route::post('upload-file', [UploadController::class, 'uploadFile']);
        Route::post('handle_friend', [FriendController::class, 'handleFriend']);
        Route::post('send_message', [MessageController::class, 'sendMessage']);
        Route::post('upload_avatar', [UserController::class, 'uploadAvatar']);
        Route::post('upload_background', [UserController::class, 'uploadBackground']);
        Route::post('update_profile', [UserController::class, 'update']);
        Route::post('remove_background', [UserController::class, 'removeBackground']);
        Route::post('check-url', [UserController::class, 'checkUrl']);
        Route::put('change-url', [UserController::class, 'changeUrl']);
        Route::get('get_user', [UserController::class, 'get']);
        Route::get('{url}/get_post', [UserController::class, 'getPost']);
        Route::get('{url}/get_friend', [UserController::class, 'getFriend']);
        Route::post('{url}/handle-follow', [FollowController::class, 'handleFollow']);
        Route::post('friend/get', [FriendController::class, 'get']);
        Route::post('{url}/add-friend', [FriendController::class, 'addFriend']);
        Route::post('{url}/accept-friend', [FriendController::class, 'acceptFriend']);
        Route::post('{url}/cancel-friend', [FriendController::class, 'cancelFriend']);
        Route::post('{url}/cancel-friend-request', [FriendController::class, 'cancelFriendRequest']);
        Route::post('{url}/block-friend', [FriendController::class, 'blockFriend']);
        Route::post('{url}/unfriend', [FriendController::class, 'unfriend']);
        Route::get('suggestUser', [UserController::class, 'suggestUser']);
        Route::get('followUser', [UserController::class, 'followUser']);
        Route::group([
            'prefix' => 'relationship',
            'middleware' => 'role:viewer|admin'
        ], function () {
            Route::get('friend/store', [FriendController::class, 'store']);
            Route::post('friend/{friend}/accept', [FriendController::class, 'accept']);
            Route::post('friend/{friend}/cancel', [FriendController::class, 'cancel']);
            Route::post('user/{user}/friend', [FriendController::class, 'addFriend']);
            Route::post('user/{user}/block', [FriendController::class, 'block']);
            Route::post('user/{user}/unfriend', [FriendController::class, 'unFriend']);
            Route::post('user/{user}/unblock', [FriendController::class, 'unBlock']);
        });

        Route::group([
            'prefix' => 'message',
            'middleware' => 'role:viewer|admin'
        ], function () {
            Route::post('private-chat', [MessageController::class, 'privateMessage']);
            Route::post('chat/{room}', [MessageController::class, 'messageChat']);
            Route::get('private-chat/{id}', [MessageController::class, 'privateMessageGet']);
            Route::get('room/{room}', [MessageController::class, 'getMessageByRoom']);
            Route::get('user/{url}', [MessageController::class, 'getRoomByUrl']);
        });

        //Follow
        Route::group([
            'prefix' => 'follow',
            'middleware' => 'role:viewer|admin'
        ], function () {
            Route::get('/', [FollowController::class, 'index']);
            Route::post('store', [FollowController::class, 'store']);
            Route::get('{follow}', [FollowController::class, 'delete']);
        });
        // Post
        Route::group([
            'prefix' => 'post',
            'middleware' => 'role:viewer|admin'
        ], function () {
            Route::get('/', [PostController::class, 'index']);
            Route::post('/', [PostController::class, 'store']);
            Route::delete('{post}', [PostController::class, 'delete']);
            Route::post('{post}', [PostController::class, 'update']);
            Route::get('{post}', [PostController::class, 'get']);
            Route::get('store', [PostController::class, 'storePost']);
            Route::post('{pid}/like', [LikeController::class, 'handleLike']);
            Route::post('{post}/comment', [CommentController::class, 'store']);
            Route::get('{post}/comment', [PostController::class, 'getComment']);
            Route::get('{post}/get_like', [PostController::class, 'getLike']);
            Route::post('upload', [PostController::class, 'uploadImage']);
            Route::group([
                'prefix' => 'comment',
            ], function () {
                Route::post('{comment}/sub_comment', [SubCommentController::class, 'create']);
                Route::get('{comment}/sub_comment', [CommentController::class, 'getSubComment']);
                Route::post('{comment}/delete', [CommentController::class, 'delete']);
                Route::post('{comment}/update', [CommentController::class, 'update']);
                Route::post('{comment}/like', [LikeController::class, 'handleLikeComment']);
                Route::group([
                    'prefix' => 'sub_comment',
                ], function () {
                    Route::post('{sub_comment}/update', [SubCommentController::class, 'update']);
                    Route::post('{sub_comment}/delete', [SubCommentController::class, 'delete']);
                    Route::post('{sub_comment}/handle_like', [LikeController::class, 'handle_like_sub_comment']);
                });
            });
        });

        Route::group([
            'prefix' => 'room',
            'middleware' => 'role:viewer|admin'
        ], function () {
            Route::get('/', [RoomController::class, 'index']);
            Route::get('{room}', [RoomController::class, 'get']);
        });

        Route::group([
            'prefix' => 'thresh',
            'middleware' => 'role:viewer'
        ], function () {
            Route::get('store', [ThreshController::class, 'store']);
            Route::post('{user}/get', [ThreshController::class, 'get']);
            Route::post('{user}/create', [ThreshController::class, 'create']);
            Route::post('{thresh}/participant/get', [ThreshController::class, 'getParticipant']);
            Route::get('{thresh}/message/get', [MessageController::class, 'get']);
            Route::post('{thresh}/message/send', [MessageController::class, 'send']);
            Route::post('{room}/delete', [MessageController::class, 'deleteRoom']);
            Route::group([
                'prefix' => 'message',
            ], function () {
                Route::delete('{message}/delete', [MessageController::class, 'delete']);
                Route::patch('{message_id}/reverse', [MessageController::class, 'reverse']);
                Route::post('{message}/remove', [MessageController::class, 'remove']);
            });
        });

        Route::group([
            'prefix' => 'friend',
            'middleware' => 'role:viewer|admin'
        ], function () {
            Route::post('{friend}/accept', [FriendController::class, 'accept']);
            Route::post('{friend}/denied', [FriendController::class, 'denied']);
            Route::post('{friend}/cancel', [FriendController::class, 'cancel']);
        });

        Route::group([
            'prefix' => 'notification',
            'middleware' => 'role:viewer'
        ], function () {
            Route::post('', [NotificationFriendController::class, 'create']);
            Route::get('read', [NotificationFriendController::class, 'read']);
            Route::delete('delete', [NotificationFriendController::class, 'delete']);
            Route::get('', [NotificationController::class, 'index']);
            Route::get('unseen', [NotificationController::class, 'numberUnseen']);
            Route::get('number_unread', [NotificationController::class, 'numberUnread']);
            Route::post('{notification}', [NotificationController::class, 'read']);
        });

        Route::group([
            'prefix' => 'search',
            'middleware' => 'role:viewer|admin'
        ], function () {
            Route::post('history', [SearchController::class, 'index']);
            Route::post('get', [SearchController::class, 'search']);
            Route::delete('{value}/delete', [SearchController::class, 'delete']);
            Route::post('test', [SearchController::class, 'test']);
        });
        Route::get('{url}', [UserController::class, 'getInfo']);
    });

    Route::group([
        'middleware' => 'role:admin',
        'prefix' => 'admin'
    ], function () {
        Route::get('dashboard', [AdminUserController::class, 'index']);
        Route::group([
            'prefix' => 'user'
        ], function () {
            Route::post('getRole', [AdminUserController::class, 'getRole']);
        });
    });
});
