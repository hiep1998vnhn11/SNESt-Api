<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Guest\TestController;
use App\Http\Controllers\User\CommentController;
use App\Http\Controllers\User\PostController;
use App\Http\Controllers\User\LikeController;
use App\Http\Controllers\User\SubCommentController;
use App\Http\Controllers\User\FriendController;
use App\Http\Controllers\User\MessageController;
use App\Http\Controllers\User\RoomController;
use App\Http\Controllers\User\UserController;

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
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
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
                Route::group([
                    'prefix' => 'sub_comment',
                ], function () {
                    Route::post('{sub_comment}/update', [SubCommentController::class, 'update']);
                    Route::post('{sub_comment}/delete', [SubCommentController::class, 'delete']);
                });
            });
        });

        Route::group([
            'prefix' => 'room',
            'middleware' => 'role:viewer|admin'
        ], function () {
            Route::get('get', [RoomController::class, 'get']);
            Route::get('store', [RoomController::class, 'store']);
            Route::get('{room}/message/get', [MessageController::class, 'getByRoom']);
            Route::post('{room}/message/send', [MessageController::class, 'sendByRoom']);
            Route::post('{room}/delete', [MessageController::class, 'deleteRoom']);
            Route::group([
                'prefix' => 'message',
            ], function () {
                Route::post('{message}/delete', [MessageController::class, 'delete']);
                Route::post('{message}/remove', [MessageController::class, 'remove']);
            });
        });

        Route::group([
            'prefix' => 'friend',
            'middleware' => 'role:viewer|admin'
        ], function () {
            Route::post('{friend}/accept', [FriendController::class, 'accept']);
            Route::post('{friend}/denied', [FriendController::class, 'denied']);
        });
    });

    Route::group([
        'middleware' => 'role: admin',
        'prefix' => 'admin'
    ], function () {
    });
});
