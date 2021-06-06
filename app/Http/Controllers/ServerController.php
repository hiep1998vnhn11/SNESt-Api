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
        return 1;
    }

    public function api()
    {
        $condition = "AND ";
        $sql = "SELECT thresh.id, thresh.type, users.profile_photo_path AS photo, pt.user_id, users.url, users.full_name,
                thresh.updated_at, thresh.name
            FROM (SELECT t.type, t.id, t.updated_at, t.name
                FROM threshes t
                LEFT JOIN participants p
                    ON p.thresh_id = t.id
                WHERE p.user_id = 1
                ORDER BY t.updated_at DESC
                LIMIT 5
                OFFSET 0
            ) thresh
            LEFT JOIN participants pt
                ON pt.thresh_id = thresh.id
            LEFT JOIN users
                ON users.id = pt.user_id
            WHERE (thresh.type = 2) AND (pt.user_id <> 1)
            OR (thresh.type = 1) AND (pt.user_id = 1)
            GROUP BY thresh.id, thresh.type, thresh.updated_at, thresh.name
        ";
        $result = DB::select($sql);
        return $result;

        $threshList = Thresh::query()
            ->leftJoin('participants', 'participants.thresh_id', 'threshes.id')
            ->where('participants.user_id', 1)
            ->orderBy('threshes.updated_at', 'desc')
            ->select('threshes.type', 'threshes.id', 'threshes.updated_at')
            ->get();
        return $threshList;
        $participants = Participant::query()
            ->leftJoin('users', 'users.id', 'participants.user_id')
            ->where('participants.user_id', '<>', 1)
            ->whereIn('participants.thresh_id', DB::select("SELECT threshes.id 
            FROM threshes
            LEFT JOIN participants
                ON participants.thresh_id = threshes.id
            WHERE participants.user_id = 1"))
            ->offset(0)
            ->limit(10)
            ->get();
        return $participants;
    }
}
