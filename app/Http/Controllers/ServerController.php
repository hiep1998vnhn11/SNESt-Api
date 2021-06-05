<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Post;
use App\Models\Like;
use Illuminate\Support\Facades\DB;
use App\Models\Thresh;

class ServerController extends Controller
{
    public function index(Request $request)
    {
        $params = $request->all();
        $user = auth()->user();
        $limit = isset($request->limit) ? $request->limit : config('const.DEFAULT_PERPAGE');
        $followingList = Follow::where('user_id', 1)->pluck('followed_id');
        $followingList[] = 1;
        $posts = Post::whereIn('user_id', $followingList)
            ->withcount(['liked', 'comments'])
            ->with('images')
            ->orderBy('updated_at', 'desc')
            ->paginate($limit);
    }

    public function api()
    {
        $room = Thresh::where('threshes.type', 2)
            ->leftJoin('participants', 'participants.thresh_id', 'threshes.id')
            ->select('threshes.id')
            ->groupBy('threshes.id')
            ->get();
        return ($room);
    }
}
