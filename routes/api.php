<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Guest\TestController;
use App\Http\Controllers\Guest\SearchController as GuestSearchController;

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

use App\Http\Controllers\Notification\FriendController as NotificationFriendController;
use App\Http\Controllers\OauthController;

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
});

Route::get('test', [TestController::class, 'test']);

Route::group([
    'prefix' => 'v1'
], function () {
    Route::group([
        'prefix' => 'guest'
    ], function () {
        Route::get('user/get', [UserController::class, 'getForGuest']);
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
        Route::post('handle_friend', [FriendController::class, 'handleFriend']);
        Route::post('send_message', [MessageController::class, 'sendMessage']);
        Route::post('upload_avatar', [UserController::class, 'uploadAvatar']);
        Route::post('upload_background', [UserController::class, 'uploadBackground']);
        Route::post('update_profile', [UserController::class, 'update']);
        Route::post('remove_background', [UserController::class, 'removeBackground']);
        Route::post('check_url', [UserController::class, 'checkUrl']);
        Route::get('get_user', [UserController::class, 'getForAuth']);
        Route::get('{user}/get', [UserController::class, 'getInfo']);
        Route::post('friend/get', [FriendController::class, 'get']);

        Route::group([
            'prefix' => 'relationship',
            'middleware' => 'role:viewer'
        ], function () {
            Route::get('friend/store', [FriendController::class, 'store']);
            Route::post('friend/{friend}/accept', [FriendController::class, 'accept']);
            Route::post('friend/{friend}/cancel', [FriendController::class, 'cancel']);
            Route::post('user/{user}/friend', [FriendController::class, 'addFriend']);
            Route::post('user/{user}/block', [FriendController::class, 'block']);
            Route::post('user/{user}/unfriend', [FriendController::class, 'unFriend']);
            Route::post('user/{user}/unblock', [FriendController::class, 'unBlock']);
        });
        // Post
        Route::group([
            'prefix' => 'post',
            'middleware' => 'role:viewer|admin'
        ], function () {
            Route::post('create', [PostController::class, 'create']);
            Route::post('{post}/delete', [PostController::class, 'delete']);
            Route::post('{post}/update', [PostController::class, 'update']);
            Route::get('{post}/get', [PostController::class, 'get']);
            Route::get('store', [PostController::class, 'store']);
            Route::post('{post}/handle_like', [LikeController::class, 'handle_like']);
            Route::post('{post}/create_comment', [CommentController::class, 'create']);
            Route::get('{post}/get_comment', [PostController::class, 'getComment']);
            Route::post('upload', [PostController::class, 'uploadImage']);

            Route::group([
                'prefix' => 'comment',
            ], function () {
                Route::post('{comment}/create_sub_comment', [SubCommentController::class, 'create']);
                Route::post('{comment}/delete', [CommentController::class, 'delete']);
                Route::post('{comment}/update', [CommentController::class, 'update']);
                Route::get('{comment}/get_sub_comment', [CommentController::class, 'getSubComment']);
                Route::post('{comment}/handle_like', [LikeController::class, 'handle_like_comment']);
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
            Route::get('{user}/get', [RoomController::class, 'get']);
            Route::get('store', [RoomController::class, 'store']);
            Route::get('{room}/message/get', [MessageController::class, 'getByRoom']);
            Route::post('{room}/message/send', [MessageController::class, 'sendByRoom']);
            Route::post('{room}/delete', [MessageController::class, 'deleteRoom']);
            Route::post('{user}/create', [RoomController::class, 'create']);
            Route::group([
                'prefix' => 'message',
            ], function () {
                Route::post('{message}/delete', [MessageController::class, 'delete']);
                Route::post('{message}/remove', [MessageController::class, 'remove']);
            });
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
            Route::post('create', [NotificationFriendController::class, 'create']);
            Route::post('read', [NotificationFriendController::class, 'read']);
            Route::delete('delete', [NotificationFriendController::class, 'delete']);
            Route::post('get', [NotificationFriendController::class, 'get']);
            Route::post('number_unread', [NotificationFriendController::class, 'numberUnread']);
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
    });

    Route::group([
        'middleware' => 'role: admin',
        'prefix' => 'admin'
    ], function () {
    });
});
