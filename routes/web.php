<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;

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

Route::get('/', function () {
    return view('welcome');
});
Route::view('/login', 'login')->middleware('guest:web')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest:web')->name('login');
Route::group([
    'prefix' => 'admin',
    'middleware' => 'role:admin|super-admin,web'
], function () {
    Route::view('/dashboard', 'dashboard')->name('admin-dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin-logout');
    Route::get('/test', [AuthController::class, 'test']);
});
