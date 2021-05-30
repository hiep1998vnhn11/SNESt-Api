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
        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect(route('admin-dashboard'));
        }
        Session::flash('error', 'Email or password is wrong!');
        return redirect(route('/'));
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('/'));
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
