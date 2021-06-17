<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Post;
use App\Models\Like;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;
use App\Models\Thresh;
use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version0X;

class ServerController extends Controller
{
    public function index(Request $request)
    {
        $params = $request->all();
        $user = User::findOrFail(1);
        $limit = isset($request->limit) ? $request->limit : config('const.DEFAULT_PERPAGE');
        $followingList = $user->follows()
            ->where('status', 1)
            ->pluck('followed_id');
        $followingList[] = $user->id;
        $post = Post::where('id', 1)
            ->withCount(['images', 'comments'])
            ->with(['images', 'user'])
            ->first();

        $posts = Post::whereIn('user_id', $followingList)
            ->with(['images'])
            ->leftJoin('users', 'users.id', 'user_id')
            ->select(
                'posts.*',
                'users.full_name as user_name',
                'users.profile_photo_path as user_profile_photo_path',
                'users.url as user_url',
                DB::raw('(SELECT count(*) FROM comments WHERE posts.id = comments.post_id) as comments_count')
            )
            ->orderBy('updated_at', 'desc')
            ->paginate($limit);
        return view('welcome');
    }

    public function api()
    {
        $client = new Client(new Version0X('http://localhost:5000'));
        $client->initialize();
        $client->emit('broadcast', ['foo' => 'bar']);
        $client->close();
    }
}
