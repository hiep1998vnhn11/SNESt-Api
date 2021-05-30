<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\User\PostController;
use App\Http\Controllers\User\UserController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [ServerController::class, 'index']);
Route::get('test/{post}', [PostController::class, 'get']);
Route::get('{url}/get_friend', [UserController::class, 'getFriend']);

// Route::view('/login', 'login.index')->middleware('guest:web')->name('admin-login');
// Route::post('/login', [AuthController::class, 'login'])->middleware('guest:web')->name('admin-login');
// Route::group([
//     'prefix' => 'admin',
//     'middleware' => 'roleweb:admin|super-admin'
// ], function () {
//     Route::view('/dashboard', 'dashboard')->name('admin-dashboard');
//     Route::post('/logout', [AuthController::class, 'logout'])->name('admin-logout');
//     Route::get('/test', [AuthController::class, 'test']);
// });
