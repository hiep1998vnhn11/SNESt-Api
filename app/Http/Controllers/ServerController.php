<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Post;

class ServerController extends Controller
{
    public function index()
    {
        foreach (Post::cursor() as $post) {
            $post->uid = rand(100000000000, 99999999999999);
            $post->save();
        }
        return view('welcome');
    }

    public function api()
    {
    }
}
