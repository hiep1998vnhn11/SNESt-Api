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

Route::group([
    'prefix' => 'admin'
], function () {
    Route::get('/login', function () {
        return view('login');
    })->middleware('guest')->name('admin-login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin-login');
    Route::get('/dashboard', [AuthController::class, 'login'])->middleware('auth')->name('admin-dashboard');
    Route::get('/test', [AuthController::class, 'test']);
});
