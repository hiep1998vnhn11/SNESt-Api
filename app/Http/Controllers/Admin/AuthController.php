<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth')->except(['login', 'test']);
    }

    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect('admin/dashboard');
        } else {
            Session::flash('error', 'Email hoặc mật khẩu không đúng!');
            return redirect('admin/login');
        }
    }

    public function test()
    {
        return Auth::user();
    }
    public function dashboard()
    {
        return view('dashboard');
    }
}
